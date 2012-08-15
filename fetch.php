<?php

require('settings.php');
require('includes/Database.php');

$db = new Database();

// Connect to the database.
$db->connect();

$do = !empty($_GET['do']) ? $_GET['do'] : NULL;
switch ($do) {
    case 'save_vectors':
        try {
            $db->save_vectors($_POST);
            print json_encode(array('result' => 'success'));
        }
        catch (Exception $e) {
            print json_encode( array('result' => 'fail', 'exception' => $e->getMessage()) );
        }
        break;
    case 'delete_vector':
        try {
            $db->delete_vector($_GET['image_id'], $_GET['vector_id']);
            print json_encode( array('result' => 'success') );
        }
        catch (Exception $e) {
            print json_encode( array('result' => 'fail', 'exception' => $e->getMessage()) );
        }
        break;
    default:
        if ( !isset($do) ) {
            exit("Parameter `do` is not set.");
        } else {
            exit("Value '{$do}' for parameter `do` is unknown.");
        }
}

?>