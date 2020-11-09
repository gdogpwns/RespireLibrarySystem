<?php

/*
BEGIN SQL CONNECTION
*/
// Provide SQL info
$servername = "localhost";
$username = "root";
$password = "";

// Create SQL connection
$conn = new mysqli($servername, $username, $password);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
//echo "Connected successfully";
/*
END SQL CONNECTION
*/

// getJSON(String)
// Parameter: ISBN of book being searched
// Returns: json_decode object of book from ISBDdb
// NOTE: ISBNdb returns all notes under obj->book for example
// $title = getJSON($ISBN)->book->title
function getJSON($ISBN) {
    $url = "https://api2.isbndb.com/book/".$ISBN;
    $restKey = "44679_2faa2f0fb561a7508b9775e81b2f9c41";

    $headers = array(
        "Content-Type: application/json",
        "Authorization: " . $restKey
    );

    $rest = curl_init();
    curl_setopt($rest,CURLOPT_URL,$url);
    curl_setopt($rest,CURLOPT_HTTPHEADER,$headers);
    curl_setopt($rest,CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($rest);
    $result = json_decode($response);

    //echo $response;
    //print_r($response);
    echo("<pre>".print_r($response,true)."</pre>");
    curl_close($rest);
    return $result;
}

/*
$hunger_games = getJSON("9780439023481");
$title = $hunger_games->book->title;
*/


?>
