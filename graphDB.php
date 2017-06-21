<?php
/*
###### Graph Database Abstraction Layer ######

This module will allow keeping Graph data in a MySQL database
*/


// Setting the default timezone
date_default_timezone_set('Asia/Kolkata');


/*
Setup all the required tables in the MySQL database for
using Graph database
*/
function setupTables($conn) {
	$graphdb_entities = false;
	$graphdb_relations = false;
	$graphdb_properties = false;

	$sql1 = "CREATE TABLE IF NOT EXISTS graphdb_entities(id INT(6) AUTO_INCREMENT PRIMARY KEY, name TEXT NOT NULL);";

	$sql2 = "CREATE TABLE IF NOT EXISTS graphdb_relations(id INT(6) AUTO_INCREMENT PRIMARY KEY, e1_id INT NOT NULL,e2_id INT NOT NULL,rel_name TEXT,rel_value TEXT,rel_type VARCHAR(255), modified_on TIMESTAMP);";

	$sql3 = "CREATE TABLE IF NOT EXISTS graphdb_properties(id INT(6) AUTO_INCREMENT PRIMARY KEY,e_id INT NOT NULL,prop_name TEXT,prop_value TEXT,prop_type VARCHAR(255) NOT NULL,modified_on TIMESTAMP);";

	$errors = array(
		'graphdb_entities' => "",
		'graphdb_relations' => "",
		'graphdb_properties' => ""
		 );

	if ($conn->query($sql1)===True) {
		$graphdb_entities = true;
	}
	else {
		$errors['graphdb_entities'] = $conn->error;
	}

	if ($conn->query($sql2)===True) {
		$graphdb_relations = true;
	}
	else {
		$errors['graphdb_relations'] = $conn->error;
	}

	if ($conn->query($sql3)===True) {
		$graphdb_properties = true;
		return "ok";
	}
	else {
		$errors['graphdb_properties'] = $conn->error;
	}

	if($graphdb_entities && $graphdb_relations && $graphdb_properties === True) {
		return "ok";
	}
	else {
		return $errors;
	}
}


/*
Creates new entity with the given name
*/
function createNewEntity($conn,$name)
{
	$name = mysqli_real_escape_string($conn,$name);
	$sql = 'INSERT INTO graphdb_entities(name) VALUES("'.$name.'");';
	if($conn->query($sql)===True){
		return "ok";
	}
	else {
		return "Error in creating new entity : " . $conn->error;
	}
}


/*
Creates a label for the entity with the given id
*/
function setLabelForEntity($conn,$id,$label)
{
	$label = mysqli_real_escape_string($conn,$label);
	$time = date('Y-m-d H:i:s', time());
	$sql = 'INSERT INTO graphdb_properties(e_id,prop_name,prop_value,prop_type,modified_on) VALUES('.$id.',"","'.$label.'","l","'.$time.'");';
	if($conn->query($sql)===True){
		return "ok";
	}
	else {
		return "Error in setting label for entity : ".$conn->error;
	}
}


/*
sets the property of the entity using the id provided
*/
function setEntityProperty($conn,$id,$key,$value) 
{
	$key = mysqli_real_escape_string($conn,$key);
	$value = mysqli_real_escape_string($conn,$value);
	$time = date('Y-m-d H:i:s', time());
	$sql = 'INSERT INTO graphdb_properties(e_id,prop_name,prop_value,prop_type,modified_on) VALUES('.$id.',"'.$key.'","'.$value.'","p","'.$time.'");';
	if($conn->query($sql)===True) {
		return "ok";
	}
	else {
		return "Error in setting the entity for property : ".$conn->error;
	}
}


/*
For setting the relationship label between two given
entities.
*/
function setEntityRelationLabel($conn,$e1_id,$e2_id,$label)
{
	$label = mysqli_real_escape_string($conn,$label);
	$time = date('Y-m-d H:i:s', time());
	$sql = 'INSERT INTO graphdb_relations(e1_id,e2_id,rel_name,rel_value,rel_type,modified_on) VALUES('.$e1_id.','.$e2_id.',"","'.$label.'","l","'.$time.'");';
	if($conn->query($sql)) {
		return "ok";
	}
	else {
		return "Error in setting entity relation label : ".$conn->error;
	}
}


/*
Sets the key:value pair for a relation existing between two entities
*/
function setEntityRelationProperty($conn,$e1_id,$e2_id,$key,$value)
{
	$label = mysqli_real_escape_string($conn,$label);
	$time = date('Y-m-d H:i:s', time());
	$sql = 'INSERT INTO graphdb_relations(e1_id,e2_id,rel_name,rel_value,rel_type,modified_on) VALUES('.$e1_id.','.$e2_id.',"'.$key.'","'.$value.'","p","'.$time.'");';
	if($conn->query($sql)) {
		return "ok";
	}
	else {
		return "Error in setting entity relation label : ".$conn->error;
	}
}


/*
Gets the id for an entity by names
*/
function getIDByName($conn,$name) {
	$name = mysqli_real_escape_string($conn,$name);
	$sql = 'SELECT id FROM graphdb_entities WHERE name="'.$name.'";';
	$result = $conn->query($sql);
	$ids = [];
	while ($data = $result->fetch_assoc()) {
		array_push($ids,$data['id']);
	}
	return $ids;
}


/*
For getting the labels associated with a given entity. The 
entity is retrived by the given id.
*/
function getEntityLabels($conn,$id)
{
	$sql = 'SELECT prop_value from graphdb_properties WHERE prop_type="l" AND e_id="'.$id.'";';
	$result = $conn->query($sql);
	$labels = [];
	while ($data = $result->fetch_assoc()) {
		array_push($labels, $data['prop_value']);
	}
	return $labels;
}


/*
For getting entity's name by id
*/
function getEntityName($conn,$id)
{
	$sql = 'SELECT name from graphdb_entities WHERE id='.$id.';';
	$result = $conn->query($sql);
	$data = $result->fetch_assoc();
	var_dump($data);
	return $data['name'];
}


/*
For getting all the properties of an entity by id
*/
function getAllProperties($conn,$id)
{
	$sql = 'SELECT prop_name,prop_value FROM graphdb_properties WHERE prop_type="p" AND e_id="'.$id.'";';
	$result = $conn->query($sql);
	$properties = array();
	while ($data = $result->fetch_assoc()) {
		$properties[$data['prop_name']] = $data['prop_value'];
	}
	return $properties;
}


/*
For getting the list of properties that a relation has
*/
function getRelationProperty($conn,$e1_id,$e2_id)
{
	$e1_id = (int)$e1_id;
	$e2_id = (int)$e2_id;
	$sql = 'SELECT rel_name,rel_value FROM graphdb_relations WHERE rel_type="p" AND e1_id='.$e1_id.' AND e2_id='.$e2_id.';';
	$result = $conn->query($sql);
	$properties = array();
	while ($data = $result->fetch_assoc()) {
		$properties[$data['rel_name']] = $data['rel_value'];
	}
	return $properties;
}
?>