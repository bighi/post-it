<?php
App::import('Component', 'Postit.Postit');

class PostitTestCase extends CakeTestCase {
	
	// Geramos uma variável contendo o que seriam os dados ao enviar um post
	var $dados = array(
		'nome' => 'Nome de Usuário',
		'login' => 'usuario',
		'imagem' => array(
			'name' => 'imagem.jpg',
			'type' => 'image/jpeg',
			'error' => '0',
			'size' => 45594,
			'tmp_name' => '.'  //colocamos o diretório padrão, pra usar a imagem que já vem no plugin
		)
	);
	
	// antes de começar cada caso de teste, inicializa o componente
	function startCase() {
		$this->postit = new PostitComponent();
	}
	
	
	// testa os métodos básicos de parâmetros do Post-it para verificar se definem as variáveis de classe
	function test_basic_param_methods() {
		
		// testa o método que determina o campo do formulário
		$this->postit->imagem("imagem1");
		$this->assertEqual($this->postit->pic_field, "imagem1");
		
		// testa método que determina tamanho da imagem
		$this->postit->tamanho("100x200");
		$this->assertEqual($this->postit->pic_w, 100);
		$this->assertEqual($this->postit->pic_h, 200);
		
		// testa método que determina largura da imagem (usando string)
		$this->postit->largura("50");
		$this->assertEqual($this->postit->pic_w, 50);
		
		// testa método que determina largura da imagem (usando int)
		$this->postit->largura(60);
		$this->assertEqual($this->postit->pic_w, 60);
		
		// testa método que determina altura da imagem (usando string)
		$this->postit->altura("70");
		$this->assertEqual($this->postit->pic_h, 70);
		
		// testa método que determina altura da imagem (usando int)
		$this->postit->altura(80);
		$this->assertEqual($this->postit->pic_h, 80);
		
		// testa método que determina a pasta da imagem
		$this->postit->na_pasta("pasta");
		$this->assertEqual($this->postit->pic_folder, "img/pasta");
		
		// testa método que determina a pasta da imagem (com img/ sendo informado)
		$this->postit->na_pasta("img/pasta");
		$this->assertEqual($this->postit->pic_folder, "img/pasta");
		
		// testa método que determina nome da imagem
		$this->postit->nome("imagem01");
		$this->assertEqual($this->postit->pic_name, "imagem01");
		
		// testa método que determina se a imagem é maximizada
		$this->postit->maximiza();
		$this->assertTrue($this->postit->pic_max);
		
		// zera maximização
		$this->postit->pic_max = false;
		
		// testa método que maximiza imagem principal E miniatura
		$this->postit->maximiza_ambas();
		$this->assertTrue($this->postit->pic_max);
		$this->assertTrue($this->postit->thumb_max);
		
		// zera maximização de novo
		$this->postit->pic_max = false;
		$this->postit->thumb_max = false;
		
		// testa método que informa a criação de miniatura
		$this->postit->com_miniatura("10x20");
		$this->assertEqual($this->postit->thumb_w, 10);
		$this->assertEqual($this->postit->thumb_h, 20);
		$this->assertTrue($this->postit->thumb);
		
		// zera o valor $thumb do plugin, para garantir que o próximo teste funciona
		$this->postit->thumb = false;
		// zera também o tamanho da miniatura
		$this->postit->thumb_w = 0;
		$this->postit->thumb_h = 0;
		
		// testa método que informa criação de miniatura (sem passar tamanho)
		$this->postit->com_miniatura();
		$this->assertTrue($this->postit->thumb);
		
		// AGORA QUE CHAMOU O MÉTODO COM_MINIATURA(), OS MÉTODOS INTELIGENTES DEVERÃO MUDAR AS INFORMAÇÕES 
		// DA MINIATURA APENAS, E NÃO DA IMAGEM PRINCIPAL
		
		// testa método que muda largura da imagem. Desta vez, deverá mudar da miniatura
		$this->postit->largura("31");
		$this->assertEqual($this->postit->thumb_w, 31);
		
		// testa método que muda altura da imagem. Desta vez, deverá mudar da miniatura
		$this->postit->altura("41");
		$this->assertEqual($this->postit->thumb_h, 41);
		
		// testa método que muda nome da miniatura
		$this->postit->nome("mini");
		$this->assertEqual($this->postit->thumb_name, "mini");
		
		// testa método que muda pasta da miniatura
		$this->postit->na_pasta("minis");
		$this->assertEqual($this->postit->thumb_folder, "img/minis");
		
		// testa método que determina se a miniatura é maximizada
		$this->postit->maximiza();
		$this->assertTrue($this->postit->thumb_max);
	}
	
	// TODO: Pesquisar melhor forma de testar o upload, redimensionamento e movimentação de imagens 
	// pelo disco. Como fazer este tipo de teste de forma auto-suficiente?
	// Como garantir que o teste de upload de imagem vai funcionar em qualquer computador que ele for colocado?
	// TODO lembrar de adicionar os testes não-idiotas
}
?>