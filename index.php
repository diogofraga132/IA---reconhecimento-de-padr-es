<?php
if ($_POST) {
	$teste = $_POST['teste'];
	$imagemParaTestar = 'imagemParaTestar';
	base64ToImage($teste,$imagemParaTestar);
	converteParaJpg($imagemParaTestar);
	redimensiona($imagemParaTestar);
}

$matrizA1 = criaImagem('letras/a50x50-1.jpg');
$matrizA2 = criaImagem('letras/a50x50-2.jpg');
$matrizA3 = criaImagem('letras/a50x50-3.jpg');
$matrizB1 = criaImagem('letras/b50x50-1.jpg');
$matrizB2 = criaImagem('letras/b50x50-2.jpg');
$matrizB3 = criaImagem('letras/b50x50-3.jpg');
$matrizAteste = criaImagem('letras/a-teste.jpg');
$matrizA2teste = criaImagem('letras/a-teste2.jpg');
$matrizBteste = criaImagem('letras/b-teste.jpg');
$matrizTesteCanvas = criaImagem('nova.jpg');

$tamanhoMatriz=50;

$matrizPesos= array_fill(0, 50, array_fill(0, 50, 0));

function base64ToImage($base64_string, $output_file) {
	$file = fopen($output_file, "wb");

	$data = explode(',', $base64_string);

	fwrite($file, base64_decode($data[1]));
	fclose($file);

	return $output_file;
}

function converteParaJpg($filePath){
	$image = imagecreatefrompng($filePath);
	$bg = imagecreatetruecolor(imagesx($image), imagesy($image));
	imagefill($bg, 0, 0, imagecolorallocate($bg, 255, 255, 255));
	imagealphablending($bg, TRUE);
	imagecopy($bg, $image, 0, 0, 0, 0, imagesx($image), imagesy($image));
	imagedestroy($image);
	$quality = 100;
	imagejpeg($bg, $filePath . ".jpg", $quality);
	imagedestroy($bg);
}

function redimensiona($filename){
	$filename.='.jpg';
	$width = 50;
	$height = 50;

	list($width_orig, $height_orig) = getimagesize($filename);

	$ratio_orig = $width_orig/$height_orig;

	if ($width/$height > $ratio_orig) {
		$width = $height*$ratio_orig;
	} else {
		$height = $width/$ratio_orig;
	}

	$image_p = imagecreatetruecolor($width, $height);
	$image = imagecreatefromjpeg($filename);
	imagecopyresampled($image_p, $image, 0, 0, 0, 0, $width, $height, $width_orig, $height_orig);
	imagejpeg($image_p, 'nova.jpg', 100);

}

function criaImagem($imagem){
	$img = imagecreatefromjpeg($imagem);
	$largura = imagesx($img);
	$altura = imagesy($img);
	for ($j = 0; $j < $altura; $j++) {
		for ($i = 0; $i < $largura; $i++) {
			$rgb = imagecolorat($img, $i, $j);
			$rgb = imagecolorsforindex($img, $rgb);
			if ($rgb['red'] ==255 or $rgb['green'] == 255 or $rgb['blue']==255){
				$mat[$j][$i]=0;
			}
			else{
				$mat[$j][$i]=1;
			}
		}
	}
	return $mat;
}

function printaMatriz($matriz){
	$tamanho = array_map('count', $matriz);
	for ($j = 0; $j < $tamanho[1]; $j++){
		for ($i = 0; $i < $tamanho[2]; $i++){
			echo $matriz[$j][$i];
		}
		echo '<br/>';
	}
}


$valorA=0;
$valorB=1;
$treinado=false;
do{
	$matrizPesos = treinamento($matrizA1,$matrizPesos,$valorA);
	$matrizPesos = treinamento($matrizA2,$matrizPesos,$valorA);
	$matrizPesos = treinamento($matrizA3,$matrizPesos,$valorA);
	$matrizPesos = treinamento($matrizB1,$matrizPesos,$valorB);
	$matrizPesos = treinamento($matrizB2,$matrizPesos,$valorB);
	$matrizPesos = treinamento($matrizB3,$matrizPesos,$valorB);
	if((fs(soma($matrizA1,$matrizPesos))==$valorA)and
		(fs(soma($matrizA2,$matrizPesos))==$valorA)and
		(fs(soma($matrizA3,$matrizPesos))==$valorA)and
		(fs(soma($matrizB1,$matrizPesos))==$valorB)and
		(fs(soma($matrizB2,$matrizPesos))==$valorB)and
		(fs(soma($matrizB3,$matrizPesos))==$valorB)){
		$treinado=true;
}
}while ($treinado != true);



function qualLetra($matrizEntradas,$matrizPesos){
	if(fs(soma($matrizEntradas,$matrizPesos))==1)
		echo '<br><br>------------- é um B -------------<br><br>';
	else
		echo '<br><br>------------- é um A -------------<br><br>';
}


function treinamento($matrizEntradas,$matrizPesos,$saidaDesejada) {
		//printaMatriz($matrizPesos);
	$saida = fs(soma($matrizEntradas,$matrizPesos));
	if ($saidaDesejada != $saida) {
		foreach ($matrizPesos as $key => $peso) {
			foreach ($matrizPesos as $key1 => $peso1) {
				$matrizPesos[$key][$key1]=ajuste($matrizPesos[$key][$key1],$saidaDesejada,$saida,$matrizEntradas[$key][$key1]);	
			}
		}
	}
	return $matrizPesos;
}

function soma($matrizEntradas,$matrizPesos){
	$soma=0;
	foreach ($matrizEntradas as $key => $entrada) {
		foreach ($matrizEntradas as $key1 => $entrada1) {
			$soma+=  $matrizEntradas[$key][$key1] * $matrizPesos[$key][$key1];
		}
	}
	return $soma;
}
function ajuste($peso, $saidaDesejada, $saidaObitida, $entrada) {
	$peso = $peso +1 * ($saidaDesejada - $saidaObitida) * $entrada;
	return $peso;
}
function fs($soma){
	if($soma <= 0)
		return 0;
	else
		return 1;
}

?>
<html>
<head>
	<meta charset="utf-8" />
	<title>Ia</title>
	<script type="text/javascript" src="script.js"></script>
	<style type="text/css">
		canvas#quadro {
		}
		.mostraMatriz {
			letter-spacing: 4.5px;
			line-height: 10px;
			font-size: 10px;
			font-family: monospace;
			margin: 0 20px;

		}
		.col-6 {
			float: left;
		}
	</style>
</head>
<body>
	<div class="col-6"><canvas id="quadro" style="background:#fff;border:1px solid black;"></canvas>
		<button onclick="resetar();">Resetar</button>
		<form action="" method="POST" id="formteste">
			<input type="hidden" value="" id="teste" name="teste">
			<button type="submit" value="Enviar">Testar</button>
		</form>
	</div>
	<div class="col-6 mostraMatriz"><?php printaMatriz($matrizTesteCanvas);?></div>
	<?php echo qualLetra($matrizTesteCanvas,$matrizPesos)?>

	<script type="text/javascript">
		desenha();
		var canvas = document.getElementById('quadro');
		var imagem = document.getElementById('imagem');
		function enviaData(){
			var data = canvas.toDataURL();
			console.log (data);
			return data;
		}
	</script>
</body>
</html>

