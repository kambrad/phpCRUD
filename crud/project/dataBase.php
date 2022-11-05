<?php

$database = new mysqli("localhost", "root", "", "crud");

$databaseCreateQuery = "CREATE DATABASE IF NOT EXISTS `crud`";

$activateDatabaseQuery = $database->query($databaseCreateQuery);

$validateQuery = FALSE;

if ($activateDatabaseQuery)
{
    $validateQuery = TRUE;

    // echo "Success Query ";
} else
{
    $validateQuery = FALSE;

    echo "Failed Query";
}


$tableCreateQuery = "CREATE TABLE IF NOT EXISTS accounts (
    `account_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `account_name` varchar(255) NOT NULL UNIQUE,
    `account_password` varchar(255) NOT NULL,
    `account_date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `account_enabled` tinyint(1) UNSIGNED NOT NULL DEFAULT '1'
)ENGINE=InnoDB DEFAULT CHARSET=UTF8";

try
{
    $activateTableQuery = $database->prepare($tableCreateQuery) or die("Table Query Error");

    $activateTableQuery->execute();

    if ($activateTableQuery->store_result() == 1)
    {
        // echo "Table is inserted ";
    } else
    {
        die ("Table is not inserted " . $database->error);
    }
} 
catch(Exception $e)
{
    throw $e->getMessage("Database Error " . $database->error);
}



// Just in-case a row is deleted inside a table;

//$updateIDQuery = "UPDATE crud.accounts SET account_id = ? WHERE account_id = ?";

$deleteIDQuery = "DELETE FROM crud.accounts WHERE account_id = ?";


// <---- UPDATES QUERY ---->


// $setId = 1;
// $accountId = @value;

// $prepareUpdateIDQuery = $database->prepare($updateIDQuery);
// $prepareUpdateIDQuery->bind_param("ii", $setId, $accountId);

// if ($queryIsUpdated = $prepareUpdateIDQuery->execute())
// {
//     $queryIsUpdated = TRUE;

//     echo "Query is updated";
// }


// <---- DELETES QUERY ---->


// $deletedID = @value;

// $prepareDeleteIDQuery = $database->prepare($deleteIDQuery);
// $prepareDeleteIDQuery->bind_param("i", $deletedID);

// if ($executeDeleteIDQuery = $prepareDeleteIDQuery->execute())
// {
//     $executeDeleteIDQuery = TRUE;

//     echo "ID is deleted";
// }

?>