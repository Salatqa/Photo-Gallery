<?php
include_once 'functions.php';

function index(&$model)
{
    return 'index_view';
}

function form(&$model)
{
    $message = null;
    $model['message'] = $message;
    return 'form_view';
}

function error(&$model)
{
    return 'error_view';
}

function gallery(&$model)
{
    return 'gallery_view';
}

function login(&$model)
{
    $message = null;
    if ($_SERVER['REQUEST_METHOD'] === 'POST') 
    { 
        if (isset($_POST['password']) && isset($_POST['login'])) 
        {
            $_SESSION['logged'] = false;

            $login = $_POST['login'];
            $password = $_POST['password'];

            if (checkData($login, $password)) {
                $id = getID($login);
                $_SESSION['ID'] = $id;
                $_SESSION['logged'] = true;
                $message = 'Logowanie przebiegło pomyślnie-jesteś zalogowany.';
            } else {
                $message = 'Nie udało się zalogować. Spróbuj ponownie';
            }
        } else if (isset($_POST['logout'])) {
            $_SESSION['logged'] = false;
        }

        else $_SESSION['logged'] = true;

        $model['sad'] = $message;
        return 'login_view';
    } 

    else 
    {  
        $model['sad'] = $message;
        return 'login_view';
    }
}
function registration(&$model)
{
    $message = null;
    if ($_SERVER['REQUEST_METHOD'] === 'POST')
    {
        $login = $_POST['login'];
        $email = $_POST['email'];
        $password = $_POST['password'];
        $repeat_password = $_POST['repeat_password'];
        if(checkPassword($password, $repeat_password)==='wrong')
        {
            $message = 'Hasło i powtórzone hasło nie są identyczne. Wypełnij formularz ponownie, tak aby hasła się zgadzały';
        }

        else 
        {
            $hash = crypt($password);


            $user = [
                'login'=>$login,
                'password'=>$hash,
                'email'=>$email
            ];

            $db = get_db();

                $query = ['login' =>
                    ['$regex' => $login]
                ];

                $is_user = $db->users->find($query);

                $check = $is_user->toArray();
            
                if ($check==null)
                    $message = saveUser($user);
                else
                    $message = 'Użytkownik o takim loginie już istnieje. Wybierz proszę inny login.';
    
        }

        $model['mess'] = $message;
        return 'registration_view';
    }

    else 
        $model['mess'] = $message;
        return 'registration_view';

}

function send(&$model)
{

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_FILES['photo'])) 
    {
        $photo = $_FILES['photo'];
        $upload_dir = '/var/www/prodv/src/web/images/';
        $message = null;

        $mime_type = checkType($photo);
        $size = checkSize($photo);

        if($mime_type==='wrong' && $size!=='wrong')
        {
            $message = 'Zły format pliku. Prześlij plik w formacie png lub jpeg.';
        }

       if($size==='wrong' && $mime_type!=='wrong') 
       {
            $message = 'Twój plik jest za duży. Prześlij mniejszy plik.';
       }

       if($mime_type==='wrong' && $size==='wrong')
        {
            $message = 'Zły format pliku. Twój plik jest również za duży. Prześlij więc mniejszy plik w formacie png lub jpeg.';
        }
       
       if($mime_type !== 'wrong' && $size !== 'wrong')
       {
            $message = uploadPhoto($photo, $upload_dir);
            $watermark = $_POST['watermark'];
            $path = '/var/www/prod/src/web/static/';

            saveWatermarkAndMiniPhoto ($photo, $mime_type, $watermark, $upload_dir, $path);

            addToDB($photo);

       }

        $model['message'] = $message;

        return 'form_view';
    }
    
    else 
    {
        return 'error_view';
    }
    
}