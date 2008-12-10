<?php
/**
 * Post-It Plugin
 * Este plugin tem um único propósito: ser a solução para os problemas de upload de imagem com CakePHP.
 * Nenhum outro plugin ou componente existente era uma solução tão boa. Em geral eles existem apenas para
 * resolver um problema muito específico, e é difícil usá-los sem ter que editar o código. E um plugin 
 * não é tão bom se você precisa editá-lo pra obter o que deseja.
 * A idéia do Post-It é torná-lo um plugin fácil de usar, e que seja capaz de resolver boa parte das necessidades
 * comuns de upload e redimensionamento de imagens.
 * O Post-It faz três coisas: Ele cuida do processo de upload de uma imagem, ele pode redimensionar esta
 * imagem (se você quiser), e pode também criar uma miniatura desta imagem (também opcional). E isso 
 * permitindo que você tenha um bom controle de como a imagem será redimensionada.
 *
 * A principal forma de passar os parâmetros para o plugin é incomum no mundo do PHP, apesar de ser bastante 
 * usada entre os programadores Ruby. Os parâmetros são através de métodos encadeados.
 * Exemplo: $postit->imagem("pic")->tamanho("300x400")->na_pasta("fotos")->envia();
 *
 * Apenas um parâmetro é obrigatório. Você precisa pelo menos definir qual o campo do formulário possui a imagem 
 * que foi enviada (método imagem()). De resto, ele usa as opções padrão caso nenhum parâmetro tenha sido passado.
 * Por padrão, o sistema se baseia no relógio do sistema para gerar um nome para a imagem, mantém o tamanho 
 * original, e guarda as imagens direto na pasta webroot/img/ do seu projeto.
 *
 * Você pode definir os seguinte parâmetros para a imagem: largura, altura, pasta onde será armazenada, nome que 
 * o arquivo receberá, e se a largura e altura devem ser expandidos até o máximo informado.
 *
 * Para quem realmente quiser passar os parâmetros do jeito normal do PHP (uma longa lista de parâmetros dentro 
 * de parênteses), é possível usar a função {@link envia_imagem()}.
 *
 * Para inserir este componente no seu projeto, adicione o seguinte código no seu controller: 
 * var $components = array('Postit.Postit');
 *
 * @author Leonardo Bighi
 * @package post-it
 * @version 1.0
 * @link http://www.leonardobighi.com/post-it
 */
class PostitComponent extends Object {
	//nome do campo do formulário que contém a imagem enviada
	var $pic_field = "image";
	
	//nome da extensão do arquivo
	var $filetype = "";
	
	//dados da imagem grande
	var $pic_w = 0;
	var $pic_h = 0;
	var $pic_folder = "img/";
	var $pic_name = null;
	var $pic_max = false;
	
	//dados da miniatura
	var $thumb_w = 0;
	var $thumb_h = 0;
	var $thumb_folder = "img/miniaturas";
	var $thumb_name = null;
	var $thumb_max = false;
	var $thumb = false; //flag que indica se vai criar miniatura ou não
	
	//uma espécia de flag que determina se está referenciando a imagem grande ou a miniatura
	var $field = 'pic';
	
	/**
	 * Este método indica ao plugin qual campo do formulário contém os dados da imagem que foi enviada. Você deve 
	 * passar exatamente o mesmo nome do campo que você declarou lá no form.
	 * @param string $form_field Nome do campo do formulário que contém a imagem.
	 */
	function imagem($form_field) {
		$this->pic_field = $form_field;
		return $this;
	}
	
	/**
	 * Este método define o tamanho da imagem após o upload (opcional).
	 * Aqui você pode informar largura e altura da imagem depois do upload. Se você não informar os valores, o sistema 
	 * entende que você quer mantér o tamanho original.
	 * Se você quiser redimensionar apenas uma das dimensões e manter a outra no tamanho original, é possível. O valor
	 * que você não quiser alterar, é só informar 0 (zero). 
	 * Por exemplo, tamanho("0x300") indica que a altura será redimensionada para 300px, mas a largura 
	 * original será mantida.
	 * Se você informar tamanho("0x0") é o mesmo que não informar tamanho nenhum, e ambas as dimensões da 
	 * imagem serão mantidas.
	 * Existem também situações onde você quer redimensionar uma das dimensões para um valor fixo, e a outra 
	 * dimensão você quer que seja redimensionada pra algum valor que vai mantér a proporção original da imagem.
	 * Para obter este efeito, é só informar o valor "-1" para a dimensão que você quer que o sistema 
	 * redimensione automaticamente para manter a proporção.
	 * Exemplo: tamanho("450x-1") indica que a largura será redimensionara para 450px, e a altura será ajustada 
	 * automaticamente para mantér a proporção.
	 * Só é permitido informar -1 se a outra dimensão for superior a 0.
	 * Note que se a largura ou altura original for menor que a largura informada para redimensionamento, 
	 * então a largura/altura original será mantida. Para evitar este comportamento, basta informar o 
	 * parâmetro maximizar();
	 * @param string $size Tamanho da imagem, no formato AAAxBBB, onde AAA é um número da largura, e BBB é 
	 * um número da altura. Exemplo: '300x450'.
	 */
	function tamanho($size) {
		list($this->pic_w, $this->pic_h) = explode('x', $size);
		$this->field = 'pic';
		return $this;
	}
	
	/**
	 * Este método entende a quem você está se referindo e ativa a maximização da imagem 
	 * correspondente (imagem principal ou miniatura).
	 */
	function maximiza() {
		if($this->field == "pic") {
			return $this->maximiza_imagem();
		} elseif($this->field == "thumb") {
			return $this->maximiza_miniatura();
		}
	}
	
	/**
	 * Ativa a maximização da imagem principal
	 */
	function maximiza_imagem() {
		$this->pic_max = true;
		$this->field = "pic";
		return $this;
	}
	
	/**
	 * Ativa a maximização da miniatura
	 */
	function maximiza_miniatura() {
		$this->thumb_max = true;
		$this->field = "thumb";
		return $this;
	}
	
	/**
	 * Ativa tanto a maximização da imagem principal quanto a da miniatura
	 */
	function maximiza_ambas() {
		$this->maximiza_imagem();
		$this->maximiza_miniatura();
		return $this;
	}
	
	/**
	 * Define apenas a altura da imagem, e deixa a largura inalterada.
	 * Método inteligente, entende se está se referindo à imagem principal ou à miniatura.
	 * @param int $h Altura em pixels que a imagem terá
	 */
	function altura($h) {
		//se o campo for inválido, apenas retorna $this para o encadeamento continuar
		if( !is_numeric($h) || empty($h) || ($h < 1) ) {
			throw new Exception("Parâmetro inválido");
		}
		
		if($this->field == "pic") {
			$this->pic_h = $h;
		} elseif ($this->field == "thumb") {
			$this->thumb_h = $h;
		}
		return $this;
	}
	
	/**
	 * Define apenas a largura da imagem, e deixa a altura inalterada
	 * Método inteligente, entende se está se referindo à imagem principal ou à miniatura.
	 * @param int $w Largura em pixels que a imagem terá
	 */
	function largura($w) {
		//se o campo for inválido, apenas retorna $this para o encadeamento continuar
		if( !is_numeric($w) || empty($w) || ($w < 1) ) {
			throw new Exception("Parâmetro inválido");
		}
		
		if($this->field == "pic") {
			$this->pic_w = $w;
		} elseif ($this->field == "thumb") {
			$this->thumb_w = $w;
		}
		return $this;
	}
	
	/**
	 * Este método define a pasta onde ficará a imagem (ou a miniatura). O comando entende sozinho se 
	 * você está se referindo à imagem principal ou à miniatura.
	 * Não é obrigatório colocar "img/" na pasta, pois o plugin adiciona sozinho se você tiver colocado.
	 * Exemplo: na_pasta("fotos") e na_pasta("img/fotos") tem o mesmo efeito.
	 * @param string $folder Pasta onde a imagem será armazenada.
	 */
	function na_pasta($folder) {
		if($this->field == "pic") {
			return $this->imagem_na_pasta($folder);
		} elseif ($this->field == "thumb") {
			return $this->mini_na_pasta($folder);
		}
	}
	
	/**
	 * Este método define a pasta onde será guardada a imagem principal. 
	 * É preferível usar o método na_pasta() em vez desse.
	 * @param string $folder A pasta onde a imagem vai ficar
	 */
	function imagem_na_pasta($folder) {
		//se não encontrou img/ no começo da string, então adiciona
		if(strpos($folder, "img/") !== 0) {
			$folder = "img/" . $folder;
		}
		$this->pic_folder = $folder;
		$this->field = "pic";
		return $this;
	}
	
	/**
	 * Este método define a pasta onde será guardada a miniatura
	 * É preferível usar o método na_pasta() em vez desse.
	 * @param string $folder A pasta onde a miniatura vai ficar
	 */
	function mini_na_pasta($folder) {
		//se não encontrou img/ no começo da string, então adiciona
		if(strpos($folder, "img/") !== 0) {
			$folder = "img/" . $folder;
		}
		$this->thumb_folder = $folder;
		$this->field = "thumb";
		return $this;
	}
	
	/**
	 * Este método informa ao plugin que uma miniatura deve ser criada.
	 * Não é obrigatório informar o tamanho da miniatura neste método. Você pode usar também os comandos
	 * altura() e largura() para informar separadamente cada dimensão da miniatura a ser gerada.
	 * @param string $size Largura e altura da miniatura, separados por um x. 
	 * Exemplo: com_miniatura("90x90");
	 */
	function com_miniatura($size = null) {
		$this->thumb = true;
		
		// se $size foi passado, define tamanho da miniatura
		if(!empty($size)) {
			list($this->thumb_w, $this->thumb_h) = explode('x', $size);
		}
		
		$this->field = "thumb";
		return $this;
	}
	
	/**
	 * Define o nome que a imagem (principal ou miniatura) terá depois do upload.
	 * Método sensível ao contexto, então pode se referir tanto à imagem principal quanto à miniatura.
	 * @param string $name Nome que a imagem terá, depois de redimensionada
	 */
	function nome($name) {
		if($this->field == "pic") {
			return $this->imagem_nome($name);
		} elseif($this->field == "thumb") {
			return $this->mini_nome($name);
		}
	}
	
	/**
	 * Este método define o nome da imagem principal.
	 * É preferível usar o método nome(). Este método é automaticamente chamado quando se chama nome() 
	 * dentro do contexto da imagem principal.
	 * @param string $name Nome que a imagem principal receberá
	 */
	function imagem_nome($name) {
		$this->pic_name = $name;
		$this->field = "pic";
		return $this;
	}
	
	/**
	 * Este método define o nome da miniatura.
	 * É preferível usar o método nome(). Este método é automaticamente chamado quando se chama nome() 
	 * dentro do contexto da miniatura.
	 * @param string $name Nome que a miniatura receberá
	 */
	function mini_nome($name) {
		$this->thumb_name = $name;
		$this->field = "thumb";
		return $this;
	}
	
	//TODO remover essa função depois que terminar de desenvolver
	function miolos() {
		debug($this);
	}
	
	/**
	 * Pega os parâmetros passados e trata o upload
	 *
	 * Este é o principal método do plugin. Depois de passar todos os parâmetros através dos métodos 
	 * encadeados, é necessário chamar envia() para que a classe faça todo o trabalho de mover as imagens, 
	 * redimensionar, renomear, etc.
	 * IMPORTANTE: Mesmo definindo os parâmetros com os outros métodos, NADA acontece se você não chamar 
	 * este método no final.
	 */
	function envia() {
		$tempfolder = "/img/temp";
		
		// Verifica se os diretórios existem. Se não existirem, cria.
		if(!is_dir($this->pic_folder)) mkdir($this->pic_folder, 0777, true);
		if(!is_dir($this->thumb_folder)) mkdir($this->thumb_folder, 0777, true);
		if(!is_dir($tempfolder)) mkdir($tempfolder, 0777, true);
		
		// Encontra a extensão da imagem, e deixa em minúsculas
		$this->filetype = $this->get_file_extension($this->pic_field['name']);
		$this->filetype = low($this->filetype);
		
		// define extensões válidas, e verifica a extensão da imagem
		$extensions = array('jpeg', 'jpg', 'gif', 'png', 'bmp');
		if(array_search($this->filetype, $extensions) !== false) {
			//pega tamanho da imagem
			$img_size = getimagesize($this->pic_field['tmp_name']);
		} else {
			// extensão inválida
			throw new Exception("Extensão inválida na imagem");
			return;
		}
		
		// se não está definido nome da imagem, gera um a partir do relógio
		if( empty($this->pic_name) ) {
			$this->pic_name = str_replace(".", "", strtotime("now"));
		}
		
		// se é pra criar miniatura, mas ela não tem nome, seu nome será o da imagem com mini_ no começo.
		if( empty($this->thumb_name) ) {
			$this->thumb_name = "mini_" . $this->pic_name;
		}
		
		// define nomes finais, com extensão
		$this->pic_name = $this->pic_name . "." . $this->filetype;
		$this->thumb_name = $this->thumb_name . "." . $this->filetype;
		
		// define endereço final dos arquivos
		$tempfile = $tempfolder . "/" . $this->pic_name;
		$imagefile = $this->pic_folder . "/" . $this->pic_name;
		$thumbfile = $this->thumb_folder . "/" . $this->thumb_name;
		
		if(is_uploaded_file($this->pic_field['tmp_name'])) {
			//copia a imagem para o diretório temporário
			if( !copy($this->pic_field['tmp_name'], $tempfile) ) {
				// se der erro
				throw new Exception("Erro ao fazer upload de arquivo.");
				die("PERIGO PERIGO PERIGO");
			} else {
				// redimensiona (se necessário) e move a imagem para a pasta correta
				$this->resize_img($tempfile, $this->pic_w, $this->pic_h, $imagefile);
				
				// se for para gerar miniatura, redimensiona e move para a pasta correta
				if($this->thumb === true) {
					$this->resize_img($tempfile, $this->thumb_w, $this->thumb_h, $thumbfile);
				}
				
				// deleta a imagem temporária
				//unlink($tempfile);
			}
		}
		
		// a imagem foi enviada corretamente, então retorna o nome da imagem
		return $this->pic_name;
	} // fim do envia()
	
	/**
	 * Função que interna que cuida de redimensionar a imagem e movê-la para a pasta correta
	 *
	 * Este método pode ser considerado o "motor" deste plugin. É aqui que acontece efetivamente o 
	 * redimensionsionamento, depois que o método envia() tratou os parâmetros e preparou tudo.
	 * Este é um método protegido, e não pode ser chamado diretamente. Ele é chamado automaticamente 
	 * pelo método envia().
	 * @param string $imgname Nome da imagem que foi enviada, sem tratamento, contendo todo o caminho até ela.
	 * @param int $w Número inteiro determinando a largura em pixels da imagem
	 * @param int $h Número inteiro determinando a altura em pixels da imagem
	 * @param string $filename Nome que a imagem terá depois de redimensionada, contendo o caminho (absoluto ou 
	 * relativo) para o local onde ela ficará.
	 */
	protected function resize_img($imgname, $w, $h, $filename)	{
		// começa a gerar a imagem de acordo com a extensão
		switch($this->filetype) {
			case "jpeg":
			case "jpg":
			$img_src = imagecreatefromjpeg($imgname);
			break;
			case "gif":
			$img_src = imagecreatefromgif($imgname);
			break;
			case "png":
			$img_src = imagecreatefrompng($imgname);
			break;
	  }

		// pega a altura e largura da imagem original
		$true_width = imagesx($img_src);
		$true_height = imagesy($img_src);
		
		// se $w ou $h forem 0, eles pegam o valor original da figura
		if ($w == 0) $final_w = $true_width;
		if ($h == 0) $final_h == $true_height;
		
		// se $w (largura) for maior que 0, então há um limite
		if ($w > 0) {
			// se largura original é maior que largura máxima, diminui largura
			if ($true_width >= $w) $final_w = $w;
			
			// se largura orginal é menor que largura máxima, aí depende se vai maximizar
			if ($true_width < $w) {
				if($this->pic_max === true) $final_w = $w;	// maximiza
				else $final_w = $true_width;				// mantém original
			}
			
			// se $h for -1, ele ajusta a altura pra ficar proporcional à largura
			if($w == -1) {
				$ratio = $true_width / $final_w;
				$final_h = $true_height / $ratio;
			}	
		}
		
		// mesma coisa que o anterior, mas com o $h (altura)
		if ($h > 0) {
			// se altura original é maior que altura máxima, diminui altura
			if ($true_height >= $h) $final_h = $h;
			
			// se altura orginal é menor que altura máxima, aí depende se vai maximizar
			if ($true_height < $h) {
				if($this->pic_max === true) $final_h = $h; 	// maximiza
				else $final_h = $true_height;				// mantém altura original
			}
			
			// se $w for igual a -1, então ajusta largura pra ficar proporcional à altura
			if($w == -1) {
				$ratio = $true_height / $final_h;
				$final_w = $true_width / $ratio;
			}
		}
		
		$img_des = ImageCreateTrueColor($final_w,$final_h);
		imagecopyresampled ($img_des, $img_src, 0, 0, 0, 0, $final_w, $final_h, $true_width, $true_height);
		
		// Save the resized image
		switch($this->filetype)
		{
			case "jpeg":
			case "jpg":
			 imagejpeg($img_des,$filename,80);
			 break;
			 case "gif":
			 imagegif($img_des,$filename,80);
			 break;
			 case "png":
			 imagepng($img_des,$filename,80);
			 break;
		}
	}
	
	/**
	 * Encontra a extensão do arquivo enviado
	 */
	function get_file_extension($str) {
		$i = strrpos($str,".");
		if (!$i) { return ""; }
		$l = strlen($str) - $i;
		$ext = substr($str,$i+1,$l);
		return $ext;
	}
} 

?>
