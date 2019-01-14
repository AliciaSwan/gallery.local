<?php
$connexion = new PDO ('mysql:host=localhost; dbname=academy; charset =utf8', 'root', '');
$connexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


function reArrayFiles(&$file_post) {

    $file_ary = array();
    $file_count = count($file_post['name']);
    $file_keys = array_keys($file_post);

    for ($i=0; $i<$file_count; $i++) {
        foreach ($file_keys as $key) {
            $file_ary[$i][$key] = $file_post[$key][$i];
        }
    }

    return $file_ary;
}

if ($_FILES['file']) {
    $file_ary = reArrayFiles($_FILES['file']);

    foreach ($file_ary as $file) {
        //print 'File Name: ' . $file['name'];
        //print 'File Type: ' . $file['type'];
        //print 'File Size: ' . $file['size'];


        if(isset($_POST['submit'])){

            $fileName = $file['name'];
            $fileTmpName = $file['tmp_name'];
            $fileType = $file['type'];
            $fileError = $file['error'];
            $fileSize = $file['size'];

            //$fileName = "abc.xyz.khm.png";

            $fileExtension = strtolower(end(explode('.', $fileName)));

            $fileName = explode('.', $fileName); //разбиваю строку по точкам
            array_pop($fileName); // вырезаю посл значение
            $fileName =implode(".",$fileName); //собираю обратно в строку


            $fileName = preg_replace('/[0-9]/','', $fileName);
            $allowedExtensions = ['jpg', 'jpeg', 'png'];





            if(in_array($fileExtension, $allowedExtensions)){
                if($fileSize<5000000){
                    if($fileError===0){
                        $connexion->query("INSERT INTO `images` (`imgname`,`extension`) VALUES ('$fileName', '$fileExtension')");
                        $lastID = $connexion->query("SELECT MAX(id) FROM `images`");
                        $lastID = $lastID->fetchAll();
                        $lastID = $lastID[0][0];

                        $fileNameNew = $lastID.$fileName.".".$fileExtension;
                        $fileDestination = 'uploads/'.$fileNameNew;
                        move_uploaded_file($fileTmpName, $fileDestination);

                       // echo $fileNameNew;
                        echo "Файл успешно загружен <br>";
                    }else{
                        echo "Что то пошло не так";
                    }

                }else{
                    echo "Слишком большой размер файла <br>";
                }
            }else {
                echo "Неверный тип файла <br> ";
            }

        }

    }
}



$data = $connexion->query("SELECT * FROM `images`");
echo "<div style='display:flex; align-items:flex-end; flex-wrap:wrap;'>";
foreach ($data as $img) {

    $delete = "delete".$img['id'];
    $image = "uploads/".$img['id'].$img['imgname'].".".$img['extension'];
    if(isset($_POST[$delete])){
        $imageID = $img['id'];
        $connexion->query("DELETE FROM `academy`.`images` WHERE  id= '$imageID'");
        if(file_exists($image)){
            unlink($image);
        }
    }

    if(file_exists($image)) {
        echo "<div>";
        echo "<img width='150' height='150' src = $image >";
        echo "<form method='post'><button name='delete".$img['id']."' style='display:block; margin:auto'>Удалить</button></form></div>";
    }
}

echo "</div>";



?>
<style>
    body{width:1200px; margin: 200px auto;}
    .form{
        width: 600px;
        padding: 20px;
        border: 2px solid grey;
        border-radius : 10px;
    }
    img{margin: 20px;}
    button{
        width: 150px;
        padding: 5px;
        margin-top: 10px;
        border: 2px solid  grey;
        border-radius : 10px;
        background-color: aliceblue;}
</style>
<body>
<div class="form">
<h1>Загрузить файл</h1>
<p>Вы можете загрузить один или несколько файлов одновременно.</p>
<form action="" method="post" enctype="multipart/form-data" >
    <input type="file" name="file[]" multiple required><br>
    <button name="submit">Отправить</button>
</form>
</div>
</body>