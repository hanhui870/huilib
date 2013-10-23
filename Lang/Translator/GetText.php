<?php
namespace HuiLib\Lang\Translator;

/**
 * 语言翻译类
 * 
 * @author Zend Framework 1.1
 * @since 2013/09/24
 */
class GetText extends \HuiLib\Lang\LangBase  {
	const FILE_EXT='.mo';
	
	// Internal variables
	private $bigEndian   = false;
	private $file        = false;
	private $adapterInfo = array();
	protected $data        = array();

	/**
	 * 实际加载翻译文件的接口
	 * 
	 * @see \HuiLib\Lang\LangBase::loadLang()
	 */
	public function loadLang($locale){
		parent::loadLang($locale);
		$filePath=$this->localPath.$locale.self::FILE_EXT;
		
		$this->loadTranslationData($filePath, $locale);
		
		return $this;
	}
	
	/**
	 * Read values from the MO file
	 *
	 * @param  string  $bytes
	*/
	private function readMOData($bytes)
	{
		if ($this->bigEndian === false) {
			return unpack('V' . $bytes, fread($this->file, 4 * $bytes));
		} else {
			return unpack('N' . $bytes, fread($this->file, 4 * $bytes));
		}
	}

	/**
	 * Load translation data (MO file reader)
	 *
	 * @param  string  $filename  MO file to add, full path must be given for access
	 * @param  string  $locale    New Locale/Language to set, identical with locale identifier,
	 *                            see Zend_Locale for more information
	 * @param  array   $option    OPTIONAL Options to use
	 * @throws Zend_Translation_Exception
	 * @return array
	 */
	protected function loadTranslationData($filename, $locale)
	{
		$this->data      = array();
		$this->bigEndian = false;
		$this->file      = @fopen($filename, 'rb');
		if (!$this->file) {
			throw new \HuiLib\Error\Exception ( 'Error opening translation file \'' . $filename . '\'.' );
		}
		if (@filesize($filename) < 10) {
			@fclose($this->file);
			throw new \HuiLib\Error\Exception ( '\'' . $filename . '\' is not a gettext file' );
		}

		// get Endian
		$input = $this->readMOData(1);
		if (strtolower(substr(dechex($input[1]), -8)) == "950412de") {
			$this->bigEndian = false;
		} else if (strtolower(substr(dechex($input[1]), -8)) == "de120495") {
			$this->bigEndian = true;
		} else {
			@fclose($this->file);
			throw new \HuiLib\Error\Exception ( '\'' . $filename . '\' is not a gettext file' );
		}
		// read revision - not supported for now
		$input = $this->readMOData(1);

		// number of bytes
		$input = $this->readMOData(1);
		$total = $input[1];

		// number of original strings
		$input = $this->readMOData(1);
		$OOffset = $input[1];

		// number of translation strings
		$input = $this->readMOData(1);
		$TOffset = $input[1];

		// fill the original table
		fseek($this->file, $OOffset);
		$origtemp = $this->readMOData(2 * $total);
		fseek($this->file, $TOffset);
		$transtemp = $this->readMOData(2 * $total);

		for($count = 0; $count < $total; ++$count) {
			if ($origtemp[$count * 2 + 1] != 0) {
				fseek($this->file, $origtemp[$count * 2 + 2]);
				$original = @fread($this->file, $origtemp[$count * 2 + 1]);
				$original = explode("\0", $original);
			} else {
				$original[0] = '';
			}

			if ($transtemp[$count * 2 + 1] != 0) {
				fseek($this->file, $transtemp[$count * 2 + 2]);
				$translate = fread($this->file, $transtemp[$count * 2 + 1]);
				$translate = explode("\0", $translate);
				if ((count($original) > 1) && (count($translate) > 1)) {
					$this->data[$locale][$original[0]] = $translate;
					array_shift($original);
					foreach ($original as $orig) {
						$this->data[$locale][$orig] = '';
					}
				} else {
					$this->data[$locale][$original[0]] = $translate[0];
				}
			}
		}

		@fclose($this->file);

		$this->data[$locale][''] = trim($this->data[$locale]['']);
		if (empty($this->data[$locale][''])) {
			$this->adapterInfo[$filename] = 'No adapter information available';
		} else {
			$this->adapterInfo[$filename] = $this->data[$locale][''];
		}

		unset($this->data[$locale]['']);
		return $this->data;
	}

	/**
	 * Returns the adapter informations
	 *
	 * @return array Each loaded adapter information as array value
	 */
	public function getAdapterInfo()
	{
		return $this->adapterInfo;
	}

	/**
	 * Returns the adapter name
	 *
	 * @return string
	 */
	public function toString()
	{
		return "Gettext";
	}
}
