<?php

putenv("ORACLE_HOME=/u01/app/oracle/product/9i_32bit");
/**Classe de acesso ao banco de dados oracle, que implementa a interface acessodata */
class Oracle 
{
	var $ipBanco = "ORACLE-HOST"; /* Vari�vel referente ao endre�o IP do banco de dados */
	var $usuarioBanco = "ORACLE-USER"; /* Vari�vel referente ao usu�rio do banco de dados */
	var $senhaBanco = "ORACLE-PASS"; /* Vari�vel referente � senha do banco */
	var $conexao; /*  Vari�vel referente ao objeto de conex�o com o banco de dados */
	
	/** M�todo de uso interno para conectar ao banco de dados */	
	
	function conectar()
	{
		
		$this->conexao = ocilogon($this->usuarioBanco, $this->senhaBanco, $this->ipBanco);
	}
	
	/** M�todo de uso interno que retorna o objeto de conex�o com o banco de dados */	
	function getConexao()
	{
		return $this->conexao;
	}
	
	/** M�todo de uso interno que encerra conex�o com banco de dados */	
	function finalizarConexao()
	{
		ocilogoff($this->conexao);
	}
	
	/** M�todo p�blico de execu��o de consultas ao banco de dados. Retorna um array bidimensional com resultado obtido */	
	function executeQuery($sql)
	{
		$this->conectar();

		$statement = ociparse($this->getConexao(),$sql);
		ociexecute($statement);
		$count = ocifetchstatement($statement,$lista);
		print_r($lista);
		$valores = array();
		
		for($j=0; $j<$count; $j++)
		{
			$linha = ""; 
			$linha = array();
			
			foreach($lista as $val)
			{
				array_push($linha,$val[$j]);
			}
			
			array_push($valores,$linha);
		}

		$erro = ocierror($this->getConexao());
		if($erro)
		{
			$this->finalizarConexao();
			return addslashes($erro);
		}
		$this->finalizarConexao();
		return $valores;
	}
	
	/** M�todo p�blico de execu��o de updates, inserts, deletes. */	
	function executeUpdate($sql)
	{
		$this->conectar();
		
		$statement = ociparse($this->getConexao(),$sql);
		ociexecute($statement); 
		
		$erro = ocierror($this->getConexao());
		if($erro)
		{
			$this->finalizarConexao();
			return addslashes($erro);
		}
		$this->finalizarConexao();
		//ocicommit($this->getonexao);
		//return $this->conexao;
	}
	
}


?>