<?php 

require '../../vendor/autoload.php';

function get_db()
{
    $mongo = new MongoDB\Client(
        "mongodb://localhost:27017/wai",
        [
            'username' => 'wai_web',
            'password' => 'w@i_w3b',
        ]);

    $db = $mongo->wai;

    return $db;
}
function checkType($img)
{
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $file_name = $img['tmp_name'];
    $mime_type = finfo_file($finfo, $file_name);

    if($mime_type !== 'image/jpeg' && $mime_type !== 'image/png') $mime_type='wrong';

    return $mime_type;
}

function checkSize($img)
{
    $size = $img['size'];
    if ($size>1024*1024) $size = 'wrong';
    else $size = 'ok';

    return $size;
}

function uploadphoto($img, $dir)
{
    $name = basename($img['name']);
    $target = $dir . $name;
    $tmp_path = $img['tmp_name'];

    if(move_uploaded_file($tmp_path, $target))
    {
        $mess='Upload pliku ' . $img['name'] . ' przebiegł pomyślnie!';
    }
    else $mess= 'Coś poszło nie tak :( Upload pliku nieudany.';

    return $mess;
}

function shortenName($fullname)
{
    $photoname = explode('.', $fullname, -1);

    return $photoname[0];
}

 function saveWatermarkAndMiniPhoto ($image, $type, $mark, $dir, $location)
 {
    if($type === 'image/jpeg')
    {
        $im = imagecreatefromjpeg( $dir . $image['name']);
    }
    
    else $im = imagecreatefrompng( $dir . $image['name']);

    $textcolor = imagecolorallocatealpha($im, 0, 0, 0, 100);
    
    $name=shortenName($image['name']);
    
    $font = $location . 'Albertsthal_Typewriter.ttf';
    
    $x = imagesx($im);
    $y = imagesy($im);
    
    $mini = imagecreatetruecolor(200, 200);
    
    imagecopyresized($mini, $im, 0, 0, 0, 0, 200, 200, $x, $y);
    
    imagepng($mini, $dir . "/mini/". $name . '_mini.png');
    
    
    for ($i = 0; $i < $x; $i+=400)
    {
        for ($j = 0; $j < 2*$y; $j+=400)
        {
            imagettftext($im, 60, 45, $i, $j, $textcolor, $font, $mark);
        }
    }
    
    imagepng($im,  $dir . "/watermark/" . $name .'_watermark.png');
    
    
    imagedestroy($im);
    imagedestroy($mini);

 }

function paging ()
{
    $dir = "/var/www/prod/src/web/images/mini/";
		$dirb = "/var/www/prod/src/web/images/watermark/";
		$dir2 = "/images/mini/";
		$dir2b = "/images/watermark/";

		$a = scandir($dir);

		$b = scandir($dirb);


		$limit = 4;
		$total = count($a)-2;
		$total_pages = ceil($total / $limit);
		if (!isset($_GET['page']))
		{
			$page_number = 1;
		} else
			$page_number = $_GET['page'];

		$initial = ($page_number - 1) * $limit;

		

			for ($i=$initial+2; $i<$initial+2+$limit && $i<$total+2; $i++) 
			{
				echo "<a href='$dir2b$b[$i]'target='_blank'><img src='" . $dir2 . $a[$i] . "'/></a>";

                $db = get_db();
                $query = ['mini_name'=>
                    ['$regex' => $a[$i]]
                ];

                $photo = $db->photos->findOne($query);
                echo '<p class="text">Tytuł: '.$photo["title"].'</p>';
                echo '<p class="text">Autor: '.$photo["author"].'</p></br>';


			}   


		for($page_number = 1; $page_number<= $total_pages; $page_number++) 
		{

			echo '<a href = "?page=' . $page_number . '">' . $page_number . ' </a>';
			
		}

		if (isset($_GET['page'])) 
		{
			$current_page = $_GET['page'];

			echo '</br>';

			echo "Jesteś na stronie: " . $current_page;
		}

}

function addToDB ($im)
{
    $db = get_db();

    $title = $_POST['title'];
    $im['title'] = $title;

    $author = $_POST['author'];
    $im['author'] = $author;

    $mini_name = shortenName($im['name']) . '_mini.png';
    $im['mini_name'] = $mini_name;

    $db->photos->insertOne($im);
}

function checkPassword($pass, $rep_pass)
{
    $check='ok';
    if($pass!==$rep_pass) $check='wrong';
    return $check;
}

function saveUser($person)
{
    $db = get_db();
    $db->users->insertOne($person);

    $mess='Konto zostało uwtorzone! Od teraz możesz się logować na stronie przy użyciu loginu i hasła ustawionego przy rejestracji.';

    return $mess;
}

function checkData($log, $pass)
{
    $db = get_db();
    $query = ['login'=>
        ['$regex' => $log]
    ];

    $arr = $db->users->findOne($query);

    $hash =$arr['password'];

    $is_user = password_verify($pass, $hash);

    return $is_user;
    
}

function getID ($log)
{
    $db = get_db();
    $query = ['login'=>
        ['$regex' => $log]
    ];

    $get = $db->users->findOne($query);

    $id = $get['_id'];

    return $id;
}
