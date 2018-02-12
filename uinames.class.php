<?php

	/**
	* UInames.com için PHP class'ı.
	*
	* @author 	Alperen Türköz <dot@turkoz.me>
	* @version 	1.0
	*	
	*/

	class uiNames{


		public $url 	   = 'https://uinames.com/api/';

		public $parameters = [
			'amount' 	=> 1,
		];

		/*
		
		 * Kaç tane veri istiyorsunuz?

		*/

		public  function amount($number = 1){
			$this->parameters['amount'] = intval($number);
			return $this;
		}

		/* 

		*  Cinsiyet ne olsun? (male, female)

		*/

		public function gender($gender = ''){
			$gender = strtolower($gender);
			$genders = ['female','male',''];
			if(in_array($gender,$genders))
				$this->parameters['gender'] = $gender;
			return $this;
		}

		/* 

		*  Hangi Ülkeden Gelsin?
		*  default:random

		*/

		public function country($country = ''){
			$this->parameters['country'] = strtolower($country);
			return $this;
		}

		/* 

		*  İsimlerin uzunluğu nasıl olsun?

		*/

		public function length($min,$max){
			$this->parameters['minlen'] = intval($min);
			$this->parameters['maxlen'] = intval($max);
			return $this;
		}

		/* 

		*  Verileri çek  -	cURL  -
		*  default: Array
		*  options: Array,Object,Json

		*/

		public function fetch($dataType = 'Array'){

			$curl_url  = $this->url . "?". http_build_query($this->parameters);		
			$curl  = curl_init();
				curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
				curl_setopt($curl, CURLOPT_URL, $curl_url);
			$data = curl_exec($curl);
			curl_close($curl);

			if(ucwords($dataType) == 'Array')
				return json_decode($data,true);
			else if(ucwords($dataType) == 'Json')
				return $data;
			else if(ucwords($dataType) == 'Object')
				return json_decode($data);
			
		}

	}
 
?>