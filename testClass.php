<?php 

class testClass{

    
    public function __construct($db) {
        $this->db = $db;
        $this->db->query('SET NAMES utf8mb4');
    }

    public function nabidka(){
        $sql = "SELECT p.produktId,p.cena,pn.cz AS nazev,pp.cz AS popis FROM produkty p,produkty_nazev pn,produkty_popis pp WHERE p.produktId = pn.produktId AND p.produktId = pp.produktId ORDER by p.cena ASC";
        $sth = $this->db->prepare($sql);
        $sth->execute();
        if($sth->rowCount()){
            $dbdata = $sth->fetchAll(PDO::FETCH_ASSOC);
            return json_encode($dbdata);
        }
    }

    public function produkt($produktId){
        $sql = "SELECT p.cena,pn.cz AS nazev,pp.cz AS popis FROM produkty p,produkty_nazev pn,produkty_popis pp WHERE p.produktId = :produktId AND p.produktId = pn.produktId AND p.produktId = pp.produktId LIMIT 1";
        $sth = $this->db->prepare($sql);
        $sth->execute(Array(":produktId" => $produktId));
        if($sth->rowCount()){
            $dbdata = $sth->fetch(PDO::FETCH_ASSOC);
            return json_encode($dbdata);
        }
    }

    public function pridaniDoKosiku($produktId,$pocetKusu){
        session_start();
        //$_SESSION = [];
        if(isset($_SESSION['id_'.$produktId])){
            $_SESSION['id_'.$produktId] += $pocetKusu;
        }
        else{
            $_SESSION['id_'.$produktId] = $pocetKusu;
        }
        print_r($_SESSION);
    }


    public function kosik(){
        session_start();
        if(isset($_SESSION)){
           //echo "tu";
            if(count($_SESSION) > 0){
                $kosik = Array();
                $polozky = Array();
                $celkova_cena = 0;
                foreach($_SESSION as $key => $pocetKusu){
                    $arr = explode("_",$key);
                    $produktId = $arr[1];
                    $sql = "SELECT $produktId AS produkt_id,$pocetKusu as pocet_kusu,(p.cena * $pocetKusu) AS cena_za_polozku_celkem,pn.cz AS nazev FROM produkty p,produkty_nazev pn WHERE p.produktId = :produktId AND p.produktId = pn.produktId";
                    $sth = $this->db->prepare($sql);
                    $sth->execute(Array(":produktId" => $produktId));
                    if($sth->rowCount()){
                        $polozky[] = $dbdata = $sth->fetch(PDO::FETCH_ASSOC);
                        $celkova_cena += $dbdata['cena_za_polozku_celkem'];
                    }
                }
                $kosik['polozky'] = $polozky;
                $kosik['celkova_cena'] = $celkova_cena;
                return $kosik;
            }
        }
    }

    public function objednavka(){
        $post = json_decode(file_get_contents("php://input"));
        $sql1 = "INSERT INTO zakaznici (jmeno,prijmeni,ulice,mesto,psc,email) VALUES ('$post->jmeno','$post->prijmeni','$post->ulice','$post->mesto','$post->psc','$post->email')";
        $sth1 = $this->db->prepare($sql1);
        $sth1->execute();
        if($sth1->rowCount()){
            $idZkaznika = $this->db->lastInsertId();
            $idObjednavky = $this->idObjednavky();
            $kosik = $this->kosik();
            if($this->zalozeniObjednavky($idObjednavky,$idZkaznika,$kosik['celkova_cena'])){
                foreach($kosik['polozky'] AS $val){
                    $sql2 = "INSERT INTO objednavky_polozky (idObjednavky,nazev,pocetKusu,cenaCelkem) VALUES ($idObjednavky,'{$val['nazev']}',{$val['pocet_kusu']},{$val['cena_za_polozku_celkem']})";
                    $sth2 = $this->db->prepare($sql2);
                    $sth2->execute();
                } 
                $_SESSION = []; 
            }
        } 
    }

    private function zalozeniObjednavky($idObjednavky,$idZkaznika,$celkovaCena){
        $sql = "INSERT INTO objednavky (idObjednavky,idZakaznika,celkovaCena) VALUES ($idObjednavky,$idZkaznika,$celkovaCena)";
        $sth = $this->db->prepare($sql);
        $sth->execute();
        if($sth->rowCount()){
            return true;
        }
    }


    private function idObjednavky(){
        $sql = "SELECT MAX(idObjednavky) AS posledni_objednavka FROM objednavky";
        $sth = $this->db->prepare($sql);
        $sth->execute();
        $dbdata = $sth->fetch(PDO::FETCH_ASSOC);
        if(!$dbdata['posledni_objednavka']){
            $idObjednavky = 202100001;
        }else{
            $idObjednavky = $dbdata['posledni_objednavka'] + 1;
        }
        return $idObjednavky;
    }

    public function seznamObjednavek(){
        $sql = "SELECT o.idObjednavky,o.celkovaCena,z.jmeno,z.prijmeni FROM objednavky o,zakaznici z WHERE o.idZakaznika = z.idZakaznika ORDER BY idObjednavky ASC";
        $sth = $this->db->prepare($sql);
        $sth->execute();
        if($sth->rowCount()){
            $dbdata = $sth->fetchAll(PDO::FETCH_ASSOC);
            return $dbdata;
        }
    }

    public function detailObjednavky($idObjednavky){
        $detailObjednavky = array();
        $sql = "SELECT z.jmeno,z.prijmeni,z.ulice,z.mesto,z.psc,z.email,o.idObjednavky,o.celkovaCena FROM zakaznici z, objednavky o WHERE o.idZakaznika = z.idZakaznika LIMIT 1";
        $sth = $this->db->prepare($sql);
        $sth->execute();
        if($sth->rowCount()){
            $detailObjednavky['zakaznik'] = $sth->fetch(PDO::FETCH_ASSOC);
            $sql1 = "SELECT op.nazev,op.pocetKusu,op.cenaCelkem FROM objednavky_polozky op, objednavky o WHERE o.idObjednavky = op.idObjednavky AND o.idObjednavky = $idObjednavky ORDER BY id ASC";
            $sth1 = $this->db->prepare($sql1);
            $sth1->execute();
            if($sth->rowCount()){
                $detailObjednavky['objednavka_polozky'] = $sth1->fetchAll(PDO::FETCH_ASSOC);
                return $detailObjednavky;
            }
        }
    } 

    public function xmlUpload(){
        $target_dir = "/";
        $target_file = $target_dir . basename($_FILES["xmlFile"]["name"]);
        $tmp = $_FILES["xmlFile"]["tmp_name"];
        $xmlFile = file_get_contents($tmp, true);
        $xml = simplexml_load_file('import.xml');
        foreach($xml->SHOPITEM as $val){
            $sql = "INSERT INTO produkty (produktId,cena,img,ean) VALUES (:id,:cena,:img,:ean)";
            $sth = $this->db->prepare($sql);
            $sth->execute(Array(':id'=> $val->ID,':cena' => $val->PRICE_VAT,':img' => $val->IMGURL,':ean' => $val->EAN));
            if($sth->rowCount() == 1){
                $sth = $this->db->prepare("INSERT INTO produkty_nazev (produktId,cz) VALUES (:id,:nazev)");
                $sth->execute(Array(':id' => $val->ID,':nazev' => $val->PRODUCTNAME));
                if($sth->rowCount() == 1){
                    $sth = $this->db->prepare("INSERT INTO produkty_popis (produktId,cz) VALUES (:id,:popis)");
                    $sth->execute(Array(':id' => $val->ID,':popis' => $val->DESCRIPTION));
                }
            }
        }
        return 'ok';
    }

    public function truncateDb(){
        $this->db->query("TRUNCATE objednavky");
        $this->db->query("TRUNCATE objednavky_polozky");
        $this->db->query("TRUNCATE produkty");
        $this->db->query("TRUNCATE produkty_nazev");
        $this->db->query("TRUNCATE produkty_popis");
        $this->db->query("TRUNCATE zakaznici");
    }
}