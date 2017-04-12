<?php
require_once 'classes/Upload.class-pdo.php';
require_once 'classes/Funcoes.class.php';

$objUp = new Upload();
$objFc = new Funcoes();

if(isset($_POST['btEnviar'])){
	$objUp->queryInsert();
}

if(isset($_POST['btAlterar'])){
	$objUp->queryUpdate();
}

if(!empty($_GET['acao'])){
	switch($_GET['acao']){
		case 'edit': $slt = $objUp->querySelecionar($_GET['id']); break;
		case 'delet': $objUp->queryDelete($_GET['id']); break; 
	}
}
?>
<!DOCTYPE HTML>
<html lang="pt-br">
<head>
	<meta charset="utf-8">
	<title>Upload de Imagem e Arquivos</title>
    <link href="css/estilo.css" rel="stylesheet" type="text/css" media="all">
</head>
<body>
<div id="conteudo">
	<div id="lista">
    	<?php foreach($objUp->querySelect() as $rst){ ?>
    	<div class="linha">
        	<div class="leng"><?=$objFc->tratarCaracter($rst['legenda'], 2)?></div>
            <div class="bt">
            	<a href="?acao=edit&id=<?=$rst['idUploadArquivo']?>" title="Editar"><img src="img/ico/edite.png" width="16" height="16"></a>
                <a href="?acao=delet&id=<?=$rst['idUploadArquivo']?>" title="Deletar"><img src="img/ico/delete.png" width="16" height="16"></a>
            </div>
        </div>
        <?php } ?>
    </div>
    <div id="formulario">
    	<form action="" method="post" enctype="multipart/form-data">
        	<label>Legenda:</label><br>
            <input type="text" name="legenda" value="<?=(!empty($slt['legenda']))?($objFc->tratarCaracter($slt['legenda'], 2)):('')?>"><br>
            <input type="file" name="arquivo"><br><br>
            <input type="submit" name="<?=(!empty($_GET['acao']) == 'edit')?('btAlterar'):('btEnviar')?>" value="<?=(!empty($_GET['acao']) == 'edit')?('Alterar'):('Cadastrar')?>">
            <input type="hidden" name="id" value="<?=(!empty($slt['idUploadArquivo']))?($slt['idUploadArquivo']):('')?>">
        </form>
    </div>
    <div id="imagem">
    	<img src="upload/image/<?=(!empty($slt['arquivo']))?($slt['arquivo']):('imagem.jpg')?>" alt="<?=(!empty($slt['legenda']))?($objFc->tratarCaracter($slt['legenda'], 2)):('Nome da Imagem')?>" title="<?=(!empty($slt['legenda']))?($objFc->tratarCaracter($slt['legenda'], 2)):('Nome da Imagem')?>" >
    </div>
</div>    
</body>
</html>