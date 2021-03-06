<?php

namespace app\control;

class AppController extends \mf\control\AbstractController {
  public function __construct(){
    parent::__construct();
  }

  public function viewHome(){
    $medias = \app\model\Media::select()->get();
    if(isset($_SESSION['access_level'])){
      $vue = new \app\view\AppView($medias);
      $vue->render("home");
    }
    else{
      \mf\router\Router::executeRoute('login');
    }
  }

  public function viewProfile(){
      $user_log = $_SESSION["user_login"];
      $user = \app\model\User::where('mail', '=', $user_log)->first();
      $user_name = $user->username;
      $name = $user->name;
      $surname = $user->surname;
      $mail = $user->mail;
      $adress = $user->address;
      $city = $user->city;
      $postalcode = $user->postalcode;
      $picture = "data:image/jpeg;base64,".base64_encode($user->photo);

      $tab = array("username" => $user_name,
        "name" => $name,
        "surname" => $surname,
        "mail" => $mail,
        "adress" => $adress,
        "city" => $city,
        "postalcode" => $postalcode,
        "picture" => $picture
      );

      $vueUser = new \app\view\AppView($tab);
      $vueUser->render("profile");
  }

  public function viewModify(){
      $user_log = $_SESSION["user_login"];
      $route = new \mf\router\Router();
      $url = $route->urlFor('profile');
      $post = $this->request->post;

      $name = filter_var($post["name"],FILTER_SANITIZE_STRING);
      $surname = filter_var($post["surname"],FILTER_SANITIZE_STRING);
      $user_name = filter_var($post["user_name"],FILTER_SANITIZE_STRING);
      $mail = filter_var($post["mail"],FILTER_SANITIZE_STRING);
      $adress = filter_var($post["adress"],FILTER_SANITIZE_STRING);
      $postalcode = filter_var($post["postalcode"],FILTER_SANITIZE_STRING);
      $city = filter_var($post["city"],FILTER_SANITIZE_STRING);

      //vérifier si le mail donné est identique => si oui on ne le renvoie pas
      //il ne peut pas y avoir de doublons dans la bdd
      $mailVerif = \app\model\User::select('mail')->where('mail', '=', $user_log)->first();
      $testRef = \app\model\User::where('mail', '=', $mail)->count();
      if($mailVerif['mail'] == $mail){
        $testRef = 0;
      }
      // echo $testRef;
      // echo $mailVerif['mail'];
      // echo $mail;
      if($testRef == 0){
        //première requete car si change mail doit pouvoir etre executé
          // rien n'est executé si mail invalide
        if($_FILES['fileToUpload']['tmp_name'] != ""){
          $photo = file_get_contents($_FILES['fileToUpload']['tmp_name']);
          $user = \app\model\User::where('mail', '=', $user_log)->update(['photo' => $photo]);
        }

         $user = \app\model\User::where('mail', '=', $user_log)->update(['name' => $name,
         'surname' => $surname, 'username' => $user_name, 'mail' => $mail,
         'address' => $adress, 'city' => $city,'postalcode' => $postalcode]);
       }
      header('location: '.$url);
  }


  public function viewMedia(){
      $http = new \mf\utils\HttpRequest();
      $media = \app\model\Media::where('id', '=', $http->get['id'])->get();
      $vue = new \app\view\AppView($media);
      $vue->render('detailMedia');
  }

  public function viewBorrow(){
      $mailUser = $_SESSION['user_login'];
      $user = \app\model\User::where('mail', '=', $mailUser)->first();
      $vue = new \app\view\AppView($user);
      $vue->render('borrow');
  }
}
