<?php
use Bulutfon\Libraries\Helper;
use Bulutfon\Libraries\Repository;
use Bulutfon\OAuth2\Client\Provider\Bulutfon;
use League\OAuth2\Client\Token\AccessToken;
use Symfony\Component\HttpFoundation\Request;

error_reporting(E_ALL);
ini_set("display_errors", 1);
if (!defined("WHMCS")) die("This file cannot be accessed directly");

function bulutfon_config(){
    $configarray = array(
        "name" => "Bulutfon WHMCS Addon",
        "description" => "Bulutfon WHMCS Addon",
        "version" => "0.0.1",
        "author" => "Bulutfon",
        "language" => "turkish",
        "fields" => array(
            "clientId" => array("clientId" => "Uygulama Anahtarı", "Type" => "text", "Size" => "60", "Description" => "Bulutfon API uygulama anahtarı.", "Default" => "" ),
            "clientSecret" => array("clientSecret" => "Gizli Anahtar", "Type" => "text", "Size" => "60", "Description" => "Bulutfon API gizli anahtarı.", "Default" => "" ),
            "redirectUri" => array("redirectUri" => "Yönlendirme Adresi ", "Type" => "text", "Size" => "120", "Description" => "Bulutfon API yönlendirme adresi.", "Default" => "" ),
            "verifySSL"=>array("verifySSL"=>"SSL Doğrulama","Type"=>"dropdown","Options" =>"true,false","Description" => "SSL Doğrulaması")
        )
    );

    return $configarray;
}

function bulutfon_activate(){
    $query = "CREATE TABLE  IF NOT EXISTS `mod_bulutfon_phonenumbers` ( `id` INT(11) NOT NULL AUTO_INCREMENT,  `userid` INT(11) NOT NULL,`phonenumber` VARCHAR(20) NOT NULL, UNIQUE (phonenumber),PRIMARY KEY id(id))";
    mysql_query($query);

    $query = "CREATE TABLE  IF NOT EXISTS `mod_bulutfon_tokens` (`tokens` TEXT NOT NULL)";
    mysql_query($query);

    return array('status'=>'success','description'=>'Bulutfon succesfully activated :)');
}

function bulutfon_deactivate(){
    $query = "DROP TABLE `mod_bulutfon_phonenumbers`";
    mysql_query($query);

    $query = "DROP TABLE `mod_bulutfon_tokens`";
    mysql_query($query);

    return array('status'=>'success','description'=>'Bulutfon succesfully deactivated :(');
}

function bulutfon_upgrade(){

}

function bulutfon_smarty(){
    $smarty = new Smarty();

    $smarty->template_dir = __DIR__.'/templates/';

    $smarty->compile_dir = $GLOBALS['templates_compiledir'];

    return $smarty;
}
function bulutfon_output($vars){

    require_once "init.php";

    $repository = new Repository();

    $request = Request::createFromGlobals();

    $provider = new Bulutfon($repository->getKeys());

    $tokens = $repository->getTokens();

    $smarty = bulutfon_smarty();

    if($tokens){
        $token = new AccessToken(Helper::decamelize($tokens));
    } else {
        Helper::outputIfAjax("<a href='{$provider->getAuthorizationUrl()}' class='button'>Yetkilendir.</a>");
        Helper::redirect($provider->getAuthorizationUrl());
    }

    switch($request->get('tab','default')){

        case 'delete':

            $phone = (int)$request->get('number',false);

            if($repository->deleteNumber($phone)) Helper::json('deleted');

            Helper::json('failed');

            break;

        case 'addtouser':

            $smarty->assign('number',$request->get('number'));

            if($request->get('clientid')){
                $validator = new Valitron\Validator($_POST);

                $rules= array(
                    'required'=>array(
                        array('telefon-numarasi'),
                        array('clientid'),
                        array('value')
                    ),
                    'integer'=>array(
                        array('telefon-numarasi'),
                        array('clientid')
                    ),
                    'lengthMin'=>array(
                        array('telefon-numarasi',10)
                    ),
                    'lengthMax'=>array(
                        array('telefon-numarasi',20)
                    )
                );

                $validator->rules($rules);

                function show_errors($array,$value,$smarty){
                    $errors = "<div style='color: #a94442;background-color: #f2dede;border:1px solid #ebccd1;padding:5px'><ul style='padding:0'>";
                    if(isset($array)){
                        foreach($array as $e){
                            $errors.= "<li>{$e}</li>";
                        }
                        $smarty->assign($value,"{$errors}</ul></div>");
                    }
                }

                if($validator->validate()) {

                    $add = $repository->addNumber(
                        $request->get('clientid'),
                        $request->get('telefon-numarasi')
                    );

                    if($add){
                        $smarty->assign('success','Kayıt başarıyla eklenmiştir.');
                    } else {
                        $errors =array();
                        $errors['telefon-numarasi'] = array('Bu telefon numarası zaten kayıtlı.');
                        show_errors($errors['telefon-numarasi'],'telefon',$smarty);
                        $smarty->assign('number',$request->get('telefon-numarasi'));
                    }

                } else {
                    // really hate smarty and i am a bit lazy.
                    $errors = $validator->errors();
                    // it must be handled by smarty but i cant figure out
                    show_errors($errors['telefon-numarasi'],'telefon',$smarty);
                    show_errors($errors['clientid'],'user',$smarty);
                    show_errors($errors['value'],'user',$smarty);
                }
            }

            $smarty->display('adduser.tpl');
            break;
        default:

            $page = $request->get('page',1);

            $userid = $request->get('userid');

            $filters = [];

            $fields = true;

            if($userid){

                $smarty->assign('userid',$userid);

                $numbers = $repository->getUserNumbers($userid);

                if(!$numbers) Helper::json("<p>Kayıtlı telefon numarası bulunamadı.</p>");

                $smarty->assign('userNumbers',$numbers);

                $numbers = Helper::imp($numbers);

                $filters = array(
                    'caller_or_callee'=>$numbers,
                    'limit' => (int)$request->get('limit',10)
                );
            }

            $smarty->assign('cdrs',$provider->getCdrs($token, $filters, $page)->cdrs);

            $smarty->assign('fields',$fields);

            $smarty->assign('page',$page);

            $smarty->assign('limit',(int)$request->get('limit',10));

            Helper::outputIfAjax($smarty->fetch('cdr.tpl'));

            $smarty->display('cdr.tpl');

            break;
    }
}