<?PHP

class Database
{
	
	var $osoite;
	var $tietokanta;
	var $kayttajanimi;
	var $salasana;
	var $yhteysnumero;
	var $last_error = 'false';
	var $insert_id;
	
	var $tulos;
	
	function Database($sql_lauseke = null){
		
		$this->osoite ="";
		$this->tietokanta = "";
		$this->kayttajanimi = "";
		$this->salasana = "";
		
		$this->openDatabase($this->osoite,$this->tietokanta,$this->kayttajanimi,$this->salasana);
		
		if($sql_lauseke != null){
			$this->makeQuery($sql_lauseke);
		}
		
	}
	
	function openDatabase($osoite,$tietokanta,$kayttajanimi,$salasana)
	{
		$this->yhteysnumero = mysql_connect($osoite,$kayttajanimi,$salasana);
		mysql_select_db($tietokanta, $this->yhteysnumero);
		
	}
	
	
	
	
	function makeQuery($sql_lauseke)
	{
		
		if (!$this->tulos = mysql_query($sql_lauseke,$this->yhteysnumero))
		{
			$this->last_error = mysql_error();
			echo mysql_error();
			return false;
			
		}else{
			$this->insert_id = mysql_insert_id($this->yhteysnumero);
			return true;
		}
	}
	
	
}
?>