<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);  


use Phalcon\Mvc\Micro;
use Phalcon\Db\Adapter\Pdo\Mysql as MysqlAdapter;

$app = new Micro();
$request = new Phalcon\Http\Request();


$app["db"] = function () {
    return new MysqlAdapter(
        [
            "host"     => "",
            "username" => "",
            "password" => "",
            "dbname"   => ""
        ]
    );
};


$app->get(
    "/prihlasky/{race_year}/{race_id}",
        function($race_year,$race_id) use ($app){
	    $response = new stdClass();
            $app->response->setHeader('Access-Control-Allow-Origin', '*');
            $app->response->setHeader('Content-Type', 'application/json');
            $app->response->sendHeaders();
            require_once 'classes/prihlasky.php';
            $prihlasky = new Prihlasky($this->db,$race_year,$race_id);
                $response->podzavody = $prihlasky->VypisPodzavodu();
                $response->dalsi_udaje = $prihlasky->DalsiUdaje();
	    echo json_encode($response);
        }
);

$app->get(
    "/nabidka",
        function() use ($app){
            require_once 'testClass.php';
            $app->response->setContentType("application/json");
            $app->response->sendHeaders();
            $tc = new testClass($this->db);
            echo $tc->nabidka();
       }
);

$app->get(
    "/produkt/{produktId}",
        function($produktId) use ($app){
            require_once 'testClass.php';
            $app->response->setContentType("application/json");
            $app->response->sendHeaders();
            $tc = new testClass($this->db);
            echo $tc->produkt($produktId);
       }
);


$app->get(
    "/pridaniDoKosiku/{produktId}/{pocetKusu}",
        function($produktId,$pocetKusu) use ($app){
            require_once 'testClass.php';
            $app->response->setContentType("application/json");
            $app->response->sendHeaders();
            $tc = new testClass($this->db);
            echo $tc->pridaniDoKosiku($produktId,$pocetKusu);
       }
);

$app->get(
    "/kosik",
        function() use ($app){
            require_once 'testClass.php';
            $app->response->setContentType("application/json");
            $app->response->sendHeaders();
            $tc = new testClass($this->db);
            echo json_encode($tc->kosik());
       }
);

$app->post(
    "/objednavka",
        function() use ($app){
            require_once 'testClass.php';
            $app->response->setContentType("application/json");
            $app->response->sendHeaders();
            $tc = new testClass($this->db);
            $tc->objednavka();
            
       }
);

$app->get(
    "/seznamObjednavek",
        function() use ($app){
            require_once 'testClass.php';
            $app->response->setContentType("application/json");
            $app->response->sendHeaders();
            $tc = new testClass($this->db);
            echo json_encode($tc->seznamObjednavek());
       }
);

$app->get(
    "/detailObjednavky/{idObjednavky}",
        function($idObjednavky) use ($app){
            require_once 'testClass.php';
            $app->response->setContentType("application/json");
            $app->response->sendHeaders();
            $tc = new testClass($this->db);
            echo json_encode($tc->detailObjednavky($idObjednavky));
       }
);

$app->post(
    "/xmlUpload",
        function() use ($app){
            require_once 'testClass.php';
            $app->response->setContentType("application/json");
            $app->response->sendHeaders();
            $tc = new testClass($this->db);
            echo json_encode($tc->xmlUpload());
       }
);

$app->get(
    "/truncateDb",
        function() use ($app){
            require_once 'testClass.php';
            $app->response->setContentType("application/json");
            $app->response->sendHeaders();
            $tc = new testClass($this->db);
            $tc->truncateDb();
       }
);

$app->handle($request->getURI());
