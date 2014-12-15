#IsAvailRegistroBR

Módulo para ZF2 da ferramenta IsAvail do RegistroBR
Usada para consultar a disponibilidade/status de um domínio brasileiro.
http://registro.br/tecnologia/provedor-hospedagem.html?secao=disponibilidade

#Instalação

    php composer.phar require fabiopaiva/is-avail-registro-br:dev-master

Adicione o módulo na configuração

    return array(
        'modules' => array(
            //...
            'IsAvailRegistroBR',
            'Application'
        )
    );

##Utilização

    public function indexAction(){
        $result = $this->checkAvail('exemplo.com.br');
    }

##Exemplo completo
Para um exemplo completo, use a rota is-avail

    <?php echo $this->url('is-avail');?>

##Demonstração

http://isavail.sigweb.net.br/
