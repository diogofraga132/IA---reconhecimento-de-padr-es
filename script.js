function resetar(){
	desenha();
}
function desenha(){
	var quadro = document.getElementById('quadro');
	quadro.style.display='block';

	var largura = 500;
	var altura = 500;

	var quadro = document.getElementById("quadro");
	quadro.setAttribute("width", largura);
	quadro.setAttribute("height", altura);

	var ctx = quadro.getContext("2d");
	var desenhando = false;

	quadro.onmousedown = function (evt) {
		ctx.moveTo(evt.clientX, evt.clientY);
		desenhando = true;
	}

	quadro.onmouseup = function () {
		var x = enviaData();
		document.getElementById("teste").value = x;
		desenhando = false;                
	}
	quadro.onmousemove = function (evt) {
		if (desenhando) {
			ctx.lineTo(evt.clientX, evt.clientY);
			ctx.shadowOffsetX = 0;
			ctx.shadowOffsetY = 0;
			ctx.shadowBlur = 20;
			ctx.shadowColor = '#000';
			ctx.stroke();
		}
	}
}