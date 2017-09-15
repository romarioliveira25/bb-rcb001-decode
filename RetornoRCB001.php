<?php 

class RetornoRCB001
{
	protected $file;

	protected $header;
	protected $details = [];
	protected $barCode;
	protected $trailler;

	
	public function __construct($filePath)
	{
		$this->file = file(trim($filePath));
	}

	
	/**
	 *
	 * Process
	 *
	 * Do a loop at the return file to identify the lines relatede to header, detail and trailler, after call the related methods
	 *
	 * @return Array
	 *
	 */
	public function process()
	{
		foreach($this->file as $i => $line) 
		{
   			switch ($i) 
   			{
   				case 0:
   					/*echo "<strong>Header</strong> (Tamanho - " . strlen(trim($line)) . "): " . $line;
   					echo "<br>";*/
   					$this->header = $line;
   					break;
   				case count($this->file) - 1:
   					/*echo "<strong>Trailler</strong> (Tamanho - " . strlen(trim($line)) . "): " . $line;
   					echo "<br>";*/
   					$this->trailler = $line;
   					break;
				default:
					/*echo "<strong>Detalhes</strong> (Tamanho - " . strlen(trim($line)) . "): " . $line;
					echo $this->file[count($this->file) - 1];*/
					array_push($this->details, $line);
					break;
   			}
		}

		/*foreach($this->details as $i => $detail) 
		{
			echo "<strong>Boleto {$i}</strong> (Tamanho - " . strlen(trim($detail)) . "): " . $detail;
			echo "<br>";
		}*/
		 
		$header   = $this->header($this->header);
		$details  = $this->details($this->details);
		$trailler = $this->trailler($this->trailler);

		echo json_encode([
		 	'header'   => $header,
		 	'details'  => $details,
		 	'trailler' => $trailler,
		]);
	}

	/**
	 *
	 * HEADER A
	 *
	 * Decode the line related to 'header info' from the return file and transform in a array with decode data
	 *
	 * @param String $header
	 * @return Array
	 *
	 */
	private function header($header)
	{
		return [
			'codigo_registro' => substr($header, 0, 1),                                                         // Alfanumérico (1 caractere) posição 01-01 - assume A
			'codigo_de_remessa' => substr($header, 1, 1),                                                       // Número (1 caractere) posição 02-02 - assume 2 - retorno enviado pelo banco
			'numero_do_convenio' => substr($header, 2, 6),                                                      // Número (6 caracteres) posição 03-08
			'uso_futuro' => substr($header, 8, 1),                                                              // Alfanumérico (1 caractere) posição 09-09
			'sequencial_de_retorno_do_intercambio_eletronico_de_dados' => substr($header, 9, 9),                // Número (9 caracteres) posição 10-18
			'uso_futuro_1' => substr($header, 18, 5),                                                           // Alfanumérico (5 caracteres) posição 19-22
			'nome_da_empresa_ou_orgao' => substr($header, 23, 20),                                              // Alfanumérico (20 caracteres) posição 23-42
			'codigo_do_banco_na_compensacao_intercambiaria' => substr($header, 43, 3),                          // Número (3 caracteres) posição 43-45
			'nome_do_banco' => substr($header, 46, 20),                                                         // Alfanumérico (20 caracteres) posição 46-65
			'data_geracao_do_arquivo' => substr($header, 66, 8),                                                // Número (8 caracteres) posição 66-73 - formato AAAA/MM/DD
			'numero_sequencial_de_arquivo' => substr($header, 74, 6),                                           // Número (6 caracteres) posição 74-79
			'versao_do_leiaute_febraban' => substr($header, 80, 2),                                             // Número (2 caracteres) posição 80-81 - Versão do leiaute Febraban adotado para registro tipo G – versão 4
			'reservado_uso_futuro' => substr($header, 82, 61),                                                  // Alfanumérico (61 caracteres) posição 82-142
			'campo_vazio' => substr($header, 143, 8),                                                           // Alfanumérico (8 caracteres) posição 143-150 - No caso do comércio eletrônico, este campo será vazio
		];
	}

	/**
	 *
	 * DETALHE G
	 *
	 * Decode the line (s) related to the payment 'detail info' from the return file and transform in a array with decode data
	 *
	 * @param Array $details
	 * @return Array
	 *
	 */
	private function details($details)
	{
		$boletos = [];

		foreach ($details as $detail) 
		{
			$boleto = [
				'codigo_registro' => substr($detail, 0, 1),                                                         // Alfanumérico (1 caractere) posição 01-01 - assume “G” 
				'prefixo_agencia_creditada' => substr($detail, 1, 4),                                               // Número (4 caracteres) posição 02-05
				'digito_verificador_prefixo_agencia' => substr($detail, 5, 1),                                      // Alfanumérico (1 caractere) posição 06-06 
				'conta_corrente_creditada' => substr($detail, 6, 9),                                                // Número (9 caracteres) posição 07-15
				'digito_verificador_numero_da_conta_corrente' => substr($detail, 15, 1),                            // Alfanumérico (1 caractere) posição 16-16
				'uso_futuro' => substr($detail, 16, 5),                                                             // Alfanumérico (5 caracteres) posição 17-21
				'data_pagamento' => substr($detail, 21 , 8),                                                        // Número (8 caracteres) posição 22-29
				'data_credito' => substr($detail, 29, 8),                                                           // Número (8 caracteres) posição 30-37 - Formato AAAA/MM/DD
				'codigo_de_barras' => substr($detail, 37 , 44),                                                     // Número (44 caracteres) posição 38-81 - vide especificação 'G5-Código de Barras'
				'valor_recebido' => substr($detail, 81, 10),                                                        // Número (10 caracteres) posição 82-93
				'valor_tarifa' => substr($detail, 91, 5),                                                           // Número (5 caracteres) posição 94-100
				'numero_sequencial_de_registro' => substr($detail, 96, 8),                                          // Número (8 caracteres) posição 101-108
				'prefixo_agencia_recebedora' => substr($detail, 108, 4),                                            // Número (4 caracteres) posição 109-112
				'uso_futuro_1' => substr($detail, 112, 4),                                                          // Álfanumérico (4 caracteres) posição 113-116
				'meio_de_arrecadacao' => substr($detail, 116, 1),                                                   // Número (1 caractere) posição 117-117 - (1- Caixa, 2 - Eletrônica, 3 - Internet)
				'autenticacao_eletronica' => substr($detail, 117, 23),                                              // Alfanumérico (23 caracteres) posição 118-140
				'forma_de_recebimento' => substr($detail, 140, 1),                                                  // Número (1 caractere) posição 141-141 - (1- Dinheiro, 2 - Cheque, 3 - Não identificada)
				'uso_futuro_2' => substr($detail, 141, 9),                                                          // Álfanumérico (9 caracteres) posição 142-150
			];

			$barCodeDecoded = $this->barCode($boleto['codigo_de_barras']);

			$boleto['codigo_de_barras_decodificado'] = $barCodeDecoded;

			array_push($boletos, $boleto);
		}

		return $boletos;
	}

	/**
	 *
	 * CÓDIGO DE BARRAS G5
	 *
	 * Decode the position 'codigo_de_barras' from the array generated by details method and transform in a array with decode data
	 *
	 * @param String $barCode
	 * @return Array
	 *
	 */
	private function barCode($barCode)
	{
		return [
			'codigo_identificacao_do_produto' => substr($barCode, 0, 1),                                          // Número (1 caractere) posição 01-01 - assume 8 (arrecadação)
			'identificacao_do_seguimento_e_forma_de_identificacao_da_empresa_orgao' => substr($barCode, 1, 1),   // Número (1 caractere) posição 02-02 - assume 9
			'identificador_do_valor_real_ou_referencia' => substr($barCode, 2, 1),                               // Número (1 caractere) posição 03-03 - assume 6 (real)
			'digito_verificador_geral' => substr($barCode, 3, 1),                                                // Número (1 caractere) posição 04-04 - módulo 10
			'valor_em_reais' => substr($barCode, 4, 11),                                                         // Número (11 caracteres) posição 05-15
			'codigo_bb_na_compensacao' => substr($barCode, 15, 4),                                               // Número (4 caracteres) posição 16-19 - assume 0001
			'preenchido_com_valor_01' => substr($barCode, 19, 2),                                                // Número (2 caracteres) posição 20-21
			'codigo_do_convenio_rcb' => substr($barCode, 21, 6),                                                 // Número (6 caracteres) posição 22-27
			'numero_do_pedido' => substr($barCode, 27, 17),                                                      // Número (17 caracteres) posição 28-44 - É o valor que foi atribuído a variável refTran gerado pelo conveniado
		];
	}

	/**
	 *
	 * TRAILLER Z
	 *
	 * Decode the line (s) related to the 'trailler info' from the return file and transform in a array with decode data
	 *
	 * @param String $trailler
	 * @return Array
	 *
	 */
	private function trailler($trailler)
	{
		return [
			'codigo_do_registro' => substr($trailler, 0, 1),                                                   // (1 caractere) posição 01-01 - assume Z
			'total_de_registros_do_arquivo' => substr($trailler, 1, 6),                                        // (6 caracteres) posição 02-07 - Total de registros do arquivo, inclusive header e trailler
			'valor_total_recebido_dos_registros' => substr($trailler, 7, 17),                                  // (17 caracteres) posição 08-24
			'livre' => substr($trailler, 24, 126),                                                             // (126 caracteres) posição 25-150
		];
	}
}
