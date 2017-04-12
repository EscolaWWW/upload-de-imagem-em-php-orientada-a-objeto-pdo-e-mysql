<?php

require_once "Conexao.class-pdo.php";
require_once "Funcoes.class.php"; 

class Upload{
	//ATRIBUTOS PRIVADOS
	private $con;
	private $objfc;
	private $idUploadArquivo;
	private $tipo;
	private $legenda;
	private $arquivo;
	
	//CONSTRUTOR
	public function __construct(){
		$this->con = new Conexao();
		$this->objfc = new Funcoes();
	}
	
	//METODOS MÁGICOS
	public function __set($atributo, $valor){
		$this->$atributo = $valor;
	}
	public function __get($atributo){
		return $this->$atributo;
	}
	
	//MOSTRANDO AS INFORMAÇÕES QUE ESTÃO NA TABELA
	public function querySelect(){
		try{
			//LISTANDO AS INFORMAÇÕES
			$cst = $this->con->conectar()->prepare("SELECT `idUploadArquivo`, `legenda`, `arquivo` FROM `aula_upload_arquivos`;");
			$cst->execute();
			return $cst->fetchAll();
		}catch(PDOException $ex){
			echo '<script type="text/javascript">alert("Error: '.$ex->getMessage().'");</script>';
		}
	}
	//RESPONSAVEL POR BUSCAR NA TABLE AS FIRMALÇAO E REGISTRO SELECIONADO PARA EDIÇÃO
	public function querySelecionar($vlr){
		try{
			$this->idUploadArquivo = $vlr;
			//SELECIONADO INFORMAÇÃO
			$cst = $this->con->conectar()->prepare("SELECT `idUploadArquivo`, `legenda`, `arquivo` FROM `aula_upload_arquivos` 
													WHERE `idUploadArquivo` = :id;");
			$cst->bindParam(':id', $this->idUploadArquivo, PDO::PARAM_INT);
			$cst->execute();
			return $cst->fetch();
		}catch(PDOException $ex){
			echo '<script type="text/javascript">alert("Error: '.$ex->getMessage().'");</script>';
		}	
	}
	
	/**
	 1º PARTE (VARIAVEIS E TRY-CATCH) - ESTA PARTE SERÁ EXPLICADA NO SEGUNDO VIDEO ONDE IREI FAZER 
	 O ESSA PARTE SEM FAZER O CADASTRO DAS INFORMACOES DO UPLOAD
	**/
	public function queryInsert(){
		try{
			$this->legenda = $_POST['legenda'];
			$arquivo = $_FILES['arquivo'];
			$largura = 280;		//280px
			$altura = 180;		//180px
			$tamanho = 100000;	//1MB
			//2º PARTE (VERIFICANDO A EXISTENCIA DO ARQUIVO E FAZENDO A VALIDACAO DO MESMO COM TRÊS CONDIÇÕES)
			if(!empty($arquivo['name'])){
				//VALIDANDO O TIPO DE IMAGEM
				//echo $arquivo['type'];
				if(!preg_match('/^(image)\/(jpeg|png)$/', $arquivo['type'])){
					$error = '<script type="text/javascript">alert("Só pode ser enviado imagens (JPG e PNG).");</script>';
				}
				//VALIDANDO AS DIMENSÕES DO ARQUIVO
				$dimensoes = getimagesize($arquivo['tmp_name']);
				if($dimensoes[0] > $largura || $dimensoes[1] > $altura){
					$error = '<script type="text/javascript">alert("Esta imagem precisa está nessas dimensões 280x180.");</script>';
				}
				//VALIDANDO O TAMANHO DO ARQUIVO
				if($arquivo['size'] > $tamanho){
					$error = '<script type="text/javascript">alert("Esta imagem precisa ser menor que 1MB.");</script>';
				}
				//3º PARTE (ALTERANDO O NOME DO ARQUIVO E ENVIANDO PARA PASTA QUE LHE FOI DESTINADA)						
				if(count($error) == 0){
					$ext = pathinfo($arquivo['name']);
					$nome_imagem = $this->objfc->normalizaString($this->legenda).'.'.$ext['extension'];/**/
					//NÃO ESQUECER DE SETAR PERMIÇÃO NAS PASTA QUE IRÁ RECEBER O UPLOAD.
					$caminho_imagem = $_SERVER['DOCUMENT_ROOT'].'/aulas/upload/upload/image/'.$nome_imagem;
					move_uploaded_file($arquivo['tmp_name'], $caminho_imagem);
					//CADASTRANDO AS INFORMAÇÕES
					$cst = $this->con->conectar()->prepare("INSERT INTO `aula_upload_arquivos` (`legenda`, `arquivo`) VALUES (:legenda, :arquivo);");
					$cst->bindParam(':legenda', $this->objfc->tratarCaracter($this->legenda, 1), PDO::PARAM_STR);
					$cst->bindParam(':arquivo', $nome_imagem, PDO::PARAM_STR);
					if($cst->execute()){
						header('location: /aulas/upload/');
					}else{
						echo '<script type="text/javascript">alert("Erro em armazenar os dados");</script>';
					}
				}else{
					echo $error;
				}
			}else{
				echo '<script type="text/javascript">alert("Escolha o arquivo para Upload");</script>';
			}
		}catch(PDOException $ex){
			echo '<script type="text/javascript">alert("Error: '.$ex->getMessage().'");</script>';
		}
	}
	
	//FFZENDO A INFORMAÇÃO DA TABELA E ENVIANDO OUTRA IMAGEM E AO MESMO TEMPO DELETANDO A ANTERIOR
	public function queryUpdate(){
		try{
			$this->idUploadArquivo = $_POST['id'];
			$this->legenda = $_POST['legenda'];
			$arquivo = $_FILES['arquivo'];
			$largura = 280;		//280px
			$altura = 180;		//180px
			$tamanho = 100000;	//1MB
			//2º PARTE (VERIFICANDO A EXISTENCIA DO ARQUIVO E FAZENDO A VALIDACAO DO MESMO COM TRÊS CONDIÇÕES)
			if(!empty($arquivo['name'])){
				//VALIDANDO O TIPO DE IMAGEM
				//echo $arquivo['type'];
				if(!preg_match('/^(image)\/(jpeg|png)$/', $arquivo['type'])){
					$error = '<script type="text/javascript">alert("Só pode ser enviado imagens (JPG e PNG).");</script>';
				}
				//VALIDANDO AS DIMENSÕES DO ARQUIVO
				$dimensoes = getimagesize($arquivo['tmp_name']);
				if($dimensoes[0] > $largura || $dimensoes[1] > $altura){
					$error = '<script type="text/javascript">alert("Esta imagem precisa está nessas dimensões 280x180.");</script>';
				}
				//VALIDANDO O TAMANHO DO ARQUIVO
				if($arquivo['size'] > $tamanho){
					$error = '<script type="text/javascript">alert("Esta imagem precisa ser menor que 1MB.");</script>';
				}
				//3º PARTE (ALTERANDO O NOME DO ARQUIVO E ENVIANDO PARA PASTA QUE LHE FOI DESTINADA)						
				if(count($error) == 0){
					$rst = $this->querySelecionar($this->idUploadArquivo);
					unlink($_SERVER['DOCUMENT_ROOT'].'/aulas/upload/upload/image/'.$rst['arquivo']);
					$ext = pathinfo($arquivo['name']);
					$nome_imagem = $this->objfc->normalizaString($this->legenda).'.'.$ext['extension'];/**/
					//NÃO ESQUECER DE SETAR PERMIÇÃO NAS PASTA QUE IRÁ RECEBER O UPLOAD.
					$caminho_imagem = $_SERVER['DOCUMENT_ROOT'].'/aulas/upload/upload/image/'.$nome_imagem;
					move_uploaded_file($arquivo['tmp_name'], $caminho_imagem);
					//CADASTRANDO AS INFORMAÇÕES
					$cst = $this->con->conectar()->prepare("UPDATE `aula_upload_arquivos` SET `legenda` = :legenda, `arquivo` = :arquivo 
															WHERE `idUploadArquivo` = :idUpload;");
					$cst->bindParam(':idUpload', $this->idUploadArquivo, PDO::PARAM_INT);										
					$cst->bindParam(':legenda', $this->objfc->tratarCaracter($this->legenda, 1), PDO::PARAM_STR);
					$cst->bindParam(':arquivo', $nome_imagem, PDO::PARAM_STR);
					if($cst->execute()){
						header('location: /aulas/upload/?acao=edit&id='.$this->idUploadArquivo);
					}else{
						echo '<script type="text/javascript">alert("Erro em alterar os dados");</script>';
					}
				}else{
					echo $error;
				}
			}else{
				$rst = $this->querySelecionar($this->idUploadArquivo);
				$ext = pathinfo($rst['arquivo']);
				$nome_imagem_novo = $this->objfc->normalizaString($this->legenda).'.'.$ext['extension'];
				$nome_imagem_antigo = $rst['arquivo'];
				
				rename($_SERVER['DOCUMENT_ROOT'].'/aulas/upload/upload/image/'.$nome_imagem_antigo, 
					   $_SERVER['DOCUMENT_ROOT'].'/aulas/upload/upload/image/'.$nome_imagem_novo);
				
				$cst = $this->con->conectar()->prepare("UPDATE `aula_upload_arquivos` SET `legenda` = :legenda, `arquivo` = :arquivo 
															WHERE `idUploadArquivo` = :idUpload;");
				$cst->bindParam(':idUpload', $this->idUploadArquivo, PDO::PARAM_INT);										
				$cst->bindParam(':legenda', $this->objfc->tratarCaracter($this->legenda, 1), PDO::PARAM_STR);
				$cst->bindParam(':arquivo', $nome_imagem_novo, PDO::PARAM_STR);
				if($cst->execute()){
					header('location: /aulas/upload/?acao=edit&id='.$this->idUploadArquivo);
				}else{
					echo '<script type="text/javascript">alert("Erro em alterar os dados");</script>';
				}	
			}						
		}catch(PDOException $ex){
			echo '<script type="text/javascript">alert("Error: '.$ex->getMessage().'");</script>'; 
		}
	}
	
	//DELETANDO A INFORMAÇÃO DA TABELA E A IMAGEM
	public function queryDelete($vlr){
		try{
			$this->idUploadArquivo = $vlr;
			//REMOVENDO A IMAGEM
			$rst = $this->querySelecionar($this->idUploadArquivo);
			unlink($_SERVER['DOCUMENT_ROOT'].'/aulas/upload/upload/image/'.$rst['arquivo']);
			//DELETANDO A INFORMAÇÃO DO TABELA
			$cst = $this->con->conectar()->prepare("DELETE FROM `aula_upload_arquivos` WHERE `idUploadArquivo` = :idUpload;");			
			$cst->bindParam(':idUpload', $this->idUploadArquivo, PDO::PARAM_INT);
			if($cst->execute()){
				header('location: /aulas/upload/');
			}else{
				echo '<script type="text/javascript">alert("Erro em deletar deletado!");</script>';
			}
		}catch(PDOException $ex){
			echo '<script type="text/javascript">alert("Error: '.$ex->getMessage().'");</script>';
		}		
	}
	
}

?>