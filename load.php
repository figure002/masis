<?php

$root = dirname( __FILE__ );
define('ROOT', $root);

require("$root/settings.php");
require("$root/includes/WebStart.php");

require("$root/includes/Database.php");
$db = new Database();
$db->connect();

require_once("$root/includes/Member.php");
$member = new Member();

if ( $member->sessionIsSet() != true ) {
    exit("Permission denied. Please login.");
}

$do = !empty($_GET['do']) ? $_GET['do'] : NULL;
switch ($do) {

    /** Loading data **/

    case 'get_file_list':
        $dir = !empty($_POST['dir']) ? urldecode($_POST['dir']) : '/data/';
        require("$root/includes/HTML.php");
        $html = new HTML();
        print $html->get_file_list(Config::read('base_path'), $dir);
        break;

    case 'get_image_info':
        try {
            if ( empty($_GET['path']) ) throw new Exception( "Parameter `path` is not set." );
            require("$root/includes/JSON.php");
            $json = new JSON();
            print $json->get_image_info($_GET['path']);
        }
        catch (Exception $e) {
            print json_encode( array('result' => 'fail', 'exception' => $e->getMessage()) );
        }
        break;
    case 'get_species':
        if ( !empty($_GET['term']) ) {
            $searchpar = !empty($_GET['searchpar']) ? $_GET['searchpar'] : 0;
            require("$root/includes/JSON.php");
            $json = new JSON();
            print $json->get_species_from_worms($_GET['term'], $searchpar);
        }
        break;
    case 'get_substrate_types':
        require("$root/includes/JSON.php");
        $json = new JSON();
        print $json->get_substrate_types();
        break;
    case 'get_image_tag_types':
        require("$root/includes/JSON.php");
        $json = new JSON();
        print $json->get_image_tag_types();
        break;
    case 'get_vectors':
        if ( empty($_GET['image_id']) ) exit("Parameter `image_id` is not set.");
        require("$root/includes/JSON.php");
        $json = new JSON();
        print $json->get_vectors($_GET['image_id']);
        break;
    case 'get_substrate_annotations':
        if ( empty($_GET['image_id']) ) exit("Parameter `image_id` is not set.");
        require("$root/includes/JSON.php");
        $json = new JSON();
        print $json->get_substrate_annotations($_GET['image_id']);
        break;
    case 'get_image_tags':
        if ( empty($_GET['image_id']) ) exit("Parameter `image_id` is not set.");
        require("$root/includes/JSON.php");
        $json = new JSON();
        print $json->get_image_tags($_GET['image_id']);
        break;

    case 'table_image_vectors':
        if ( empty($_GET['image_id']) ) exit("Parameter `image_id` is not set.");
        require("$root/includes/DataTable.php");
        $table = new DataTable();
        $table->list_image_vectors($_GET['image_id']);
        break;
    case 'table_species_coverage_where_present':
        require("$root/includes/DataTable.php");
        $table = new DataTable();
        $table->species_coverage_where_present();
        break;
    case 'table_species_coverage_overall':
        require("$root/includes/DataTable.php");
        $table = new DataTable();
        $table->species_coverage_overall();
        break;

    /** Saving data **/

    case 'save_vectors':
        try {
            $db->save_vectors($_POST);
            print json_encode( array('result' => 'success') );
        }
        catch (Exception $e) {
            print json_encode( array('result' => 'fail', 'exception' => $e->getMessage()) );
        }
        break;
    case 'delete_vector':
        try {
            if ( empty($_GET['image_id']) ) throw new Exception( "Parameter `image_id` is not set." );
            if ( empty($_GET['vector_id']) ) throw new Exception( "Parameter `vector_id` is not set." );

            $db->delete_vector($_GET['image_id'], $_GET['vector_id']);
            print json_encode( array('result' => 'success') );
        }
        catch (Exception $e) {
            print json_encode( array('result' => 'fail', 'exception' => $e->getMessage()) );
        }
        break;
    case 'set_areas':
        try {
            $count = $db->set_areas();
            print json_encode( array('result' => 'success', 'count' => $count) );
        }
        catch (Exception $e) {
            print json_encode( array('result' => 'fail', 'exception' => $e->getMessage()) );
        }
        break;
    case 'set_annotation_status':
        try {
            if ( empty($_GET['image_id']) ) throw new Exception( "Parameter `image_id` is not set." );
            if ( empty($_GET['status']) ) throw new Exception( "Parameter `status` is not set." );

            $count = $db->set_annotation_status($_GET['image_id'], $_GET['status']);
            print json_encode( array('result' => 'success') );
        }
        catch (Exception $e) {
            print json_encode( array('result' => 'fail', 'exception' => $e->getMessage()) );
        }
        break;
    case 'set_substrate_annotations':
        try {
            if ( empty($_POST['image_id']) ) throw new Exception( "Parameter `image_id` is not set." );
            $annotations = isset($_POST['annotations']) ? $_POST['annotations'] : array();

            $db->set_substrate_annotations($_POST['image_id'], $annotations);
            print json_encode( array('result' => 'success') );
        }
        catch (Exception $e) {
            print json_encode( array('result' => 'fail', 'exception' => $e->getMessage()) );
        }
        break;
    case 'set_image_tags':
        try {
            if ( empty($_POST['image_id']) ) throw new Exception( "Parameter `image_id` is not set." );
            $tags = isset($_POST['tags']) ? $_POST['tags'] : array();

            $db->set_image_tags($_POST['image_id'], $tags);
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
