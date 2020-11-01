<?php
/**
 * Created by PhpStorm.
 * User: Diego
 * Date: 2/2/2019
 * Time: 12:18 AM
 */

require __DIR__ .'/../vendor/autoload.php';
require_once 'resources/secrets.php';

$dt = new BotStatusBot\DataLogger();

$servername = "localhost";
$username = "root";
$password = "";

// Create connection
$conn = new mysqli($servername, $username, $password);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

//"Connected successfully";

$sql = "INSERT INTO bas_database.bot_list (name) VALUES ('John')";

if ($conn->query($sql) === TRUE) {
    echo "New record created successfully";
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}

$conn->close();

//https://botappreciationsociety.fandom.com/api.php?action=query&prop=revisions&rvprop=content&format=xmlfm&titles=FactpostBot4286&rvsection=0
//https://botappreciationsociety.fandom.com/api.php?action=query&prop=categories&titles=PaintBot
//https://botappreciationsociety.fandom.com/api.php?action=query&list=categorymembers&cmtitle=Category:Facebook%20Bots&cmlimit=500
//https://botappreciationsociety.fandom.com/api/v1/Articles/AsSimpleJson?id=225