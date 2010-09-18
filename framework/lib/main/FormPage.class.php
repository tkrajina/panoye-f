<?php

/**
 * Za stranice koje na sebi imaju formu. U principu se ponasa potpuno isto
 * kao {@link Page} ali ima par dodatnih metoda za prikaz i validaciju
 * forme.
 *
 * Važno: Svaka forma mora imati objekt u kojemu se čuvaju podaci. Taj
 * objekt treba inicijalizirati negdje u {@link #execute()}.
 */
class FormPage extends Page {

	private $multipartForm = false;

	private $errors = array();

	private $formFields = array();

	private $errorsPrinted = false;

    public function __construct() {
    	parent::__construct();
    }

	public function executePage() {
		$this->execute();
		$this->afterExecute();

		if( $this->isRedirect() ) {
			// Ako je redirekt => ne idemo dalje:
			return;
		}

		if( $this->isSubmit() ) {
			foreach( $this->postParameters as $key => $value ) {
				$value = str_replace( "\'", htmlspecialchars( "'" ), $value );
				$value = str_replace( '\\"', htmlspecialchars( '"' ), $value );
				$this->postParameters[ $key ] = $value;
			}
			$this->getObject()->addProperties( $this->preparePropertiesForObject( $this->postParameters ) );
			$this->validate();
			if( $this->hasErrors() ) {
//				global $visit;
//				$visit->setFormResponse( $visit->prepareArray( $this->getErrors() ) );
			}
			else {
//				global $visit;
//				$visit->setFormResponse( 'OK' );
				$this->submit();

				// Ako je sve uredno prošlo sa submitom onda se i briše cache (jer
				// je stranica možda promijenjena)
				$this->deleteCache();
			}
		}
	}

	private function preparePropertiesForObject( $properties ) {
		@ $object =& $this->getObject();
		$types = $object->getTableColumns();
		$result = array();
		foreach( $properties as $key => $value ) {
			$type = @$types[ $key ];
			if( $type == AppObject::TIMESTAMP ) {
				$result[ $key ] = new Timestamp( $value );
			}
			else if( $type == AppObject::DATE ) {
				$result[ $key ] = new Date( $value );
			}
			else {
				$result[ $key ] = $value;
			}
		}
		return $result;
	}

	/** @see Page#execute() */
	public function execute() {
	}

	/** Sve validacije. */
	protected function validate() {}

	/** Metoda koja se poziva ako je uredno prosla validacija. */
	public function submit() {
	}

	/**
	 * Ako će na stranici biti samo forma iz {@link #form()} onda ovu metodu treba ostaviti
	 * kakva jest, inače -- prepisati i pozvati {@link #executeForm()} na mjestu gdje ju se
	 * želi ispisati.
	 *
	 * @see Page#view()
	 */
	public function printMain() {
	}

	// ------------------------------------------------------------------------
	// Validacija objekta
	// ------------------------------------------------------------------------

	/**
	 * Svaki objekt moze imati svoje metode za validaciju. Ukoliko ih ima
	 * kod validacije se mogu automatski pozvati te metode za validaciju
	 * polja iz forme. U {@link #validate()} treba samo na pocetku pozvati
	 * $this->validateObject()
	 */
	protected function validateObject( $fields = null ) {
		@ $object = $this->getObject();
		if( ! is_array( $fields ) ) {
			$fields = array_keys( $this->postParameters );
		}
		foreach( $fields as $field ) {
			$method = 'validate' . $field;
			if( method_exists( $object, $method ) ) {
				$result = $object->$method();
				if( is_string( $result ) ) {
					$this->addError( $field, $result );
				}
			}
		}
	}

	// ------------------------------------------------------------------------
	// Funkcije za validaciju:
	// ------------------------------------------------------------------------

	protected function addError( $errorOrProperty, $error = null ) {
		if( $error == null ) {
			$this->errors[] = $errorOrProperty;
		}
		else {
			if( is_array( $errorOrProperty ) ) {
				foreach( $errorOrProperty as $p ) {
					$this->errors[ $p ] = $error;
				}
			}
			else {
				$this->errors[ $errorOrProperty ] = $error;
			}
		}
	}

	protected function addErrorIfNot( $expression, $errorOrProperty, $error = null ) {
		if( ! $expression ) {
			$this->addError( $errorOrProperty, $error );
		}
	}

	protected function addErrorIf( $expression, $errorOrProperty, $error = null ) {
		if( $expression ) {
			$this->addError( $errorOrProperty, $error );
		}
	}

	private function isError( $property ) {
//		d( $property );
//		ddie( $this->errors );
		foreach( $this->errors as $key => $value ) {
			if( strtolower( $key ) == strtolower( $property ) ) {
				return true;
			}
		}
		return false;
	}

	protected function getErrors() {
		return $this->errors;
	}

	protected function hasErrors() {
		return @sizeof( $this->errors ) > 0;
	}

	protected function printErrors() {
		echo '<ul class="error" id="errors">';
?>
<script language='JavaScript'>
function gotoErrors() {
	if( document.location.search( "#errors" ) < 0 ) {
		document.location += "#errors";
	}
}
setTimeout( "gotoErrors()", 1500 );
</script>
<?
		$errors = array_unique( array_values( $this->errors ) );

		foreach( $errors as $error ) {
			echo '<li class="error">' . $error . '</li>';
		}
		echo '</ul>';
	}

	// ------------------------------------------------------------------------
	// Metode za upload datoteka:
	// ------------------------------------------------------------------------

	/**
	 * Array with uploaded file fields (not filenames).
	 */
	protected function getUploadedFiles() {
		return array_keys( $_FILES );
	}

	protected function isUploaded( $fileName ) {
		$s = $this->getFileSize( $fileName );
		return $s && ( (int) $s ) > 0;
	}

	protected function getFileSize( $fileName ) {
		if( $temp = @$_FILES[ $fileName ] ) {
			return $temp[ 'size' ];
		}
		else {
			return 0;
		}
	}

	protected function getFileName( $fileName ) {
		if( $temp = @$_FILES[ $fileName ] ) {
			return $temp[ 'name' ];
		}
		else {
			return 0;
		}
	}

	protected function getFileType( $fileName ) {
		if( $temp = @$_FILES[ $fileName ] ) {
			return $temp[ 'type' ];
		}
		else {
			return false;
		}
	}

	protected function getTempFile( $fileName ) {
		if( $temp = @$_FILES[ $fileName ] ) {
			return $temp[ 'tmp_name' ];
		}
		else {
			return false;
		}
	}

	/**
	 * Kopira uploadani fajl na novu poziciju.
	 */
	protected function copyFile( $fileName, $destination ) {
		if( $temp = @$_FILES[ $fileName ] ) {
			return (bool) @move_uploaded_file( $temp[ 'tmp_name' ],  $destination );
		}
		else {
			return false;
		}
	}

	// ------------------------------------------------------------------------
	// Funkcije za kreiranje same forme:
	// ------------------------------------------------------------------------

	protected function formStart() {
		$this->formErrors();
		echo '<form id="Form" method="POST">';
	}

	protected function formStartMultipart() {
		$this->formErrors();
		$this->multipartForm = true;
		echo '<form id="Form" method="POST" enctype="multipart/form-data">';
	}

	protected function formEnd() {
		$fields = array();
		foreach( $this->formFields as $field ) {
			global $queryString;
			// Hash je vezan uz stranicu, tajni string (ovdje site description) i polje...
			// na taj nacin ne ovisi o session id-u i moze se koristiti i u formama koje su
			// na keširanim stranicama:
			$hash = md5( Application::SECRET_APP_KEY . $queryString . $field );
			$fields[] = $hash . ':' . $field;
		}
		echo '<input type="hidden" name="_fields" value="' . implode( ',', $fields ) . '"/>';
		echo '</form>';
	}

	protected function formErrors() {
		if( $this->errorsPrinted ) {
			return;
		}
		$this->errorsPrinted = true;

		if( $this->hasErrors() ) {
			$this->printErrors();
		}
	}

	private function registerField( $fieldName ) {
		$this->formFields[] = $fieldName;
	}

	protected function formText( $property, $parameters = '' ) {
		$this->registerField( $property );
		$value = $this->getObject()->get( $property );
		if( $this->isError( $property ) ) {
			Tags::open( 'span', array( 'class' => 'error' ) );
		}
		echo '<input type="text" name="' . $property . '" id="' . $property . '" value="' . $value . '" ' . $parameters . ' />';
		Tags::close( 'span' );
	}

	protected function formHidden( $property, $parameters = '' ) {
		$this->registerField( $property );
		$value = $this->getObject()->get( $property );
		echo '<input type="hidden" name="' . $property . '" id="' . $property . '" value="' . $value . '" ' . $parameters . ' />';
	}

	protected function formPassword( $property, $parameters = '' ) {
		$this->registerField( $property );
		$value = $this->getObject()->get( $property );
		if( $this->isError( $property ) ) {
			Tags::open( 'span', array( 'class' => 'error' ) );
		}
		echo '<input type="password" name="' . $property . '" id="' . $property . '" ' . $parameters . ' />';
		Tags::close( 'span' );
	}

	protected function formCheckbox( $property, $parameters = '' ) {
		$this->registerField( $property );
		$value = $this->getObject()->get( $property );

		$checked = (boolean) $value ? 'checked' : '';
		if( $this->isError( $property ) ) {
			Tags::open( 'span', array( 'class' => 'error' ) );
		}
		echo '<input type="checkbox" name="' . $property . '" id="' . $property . '" value="1" ' . $parameters . ' ' . $checked . ' />';
		Tags::close( 'span' );
	}

	protected function formSelect( $property, $keyTextArray ) {
		$this->registerField( $property );
		$value = $this->getObject()->get( $property );
		if( $this->isError( $property ) ) {
			Tags::open( 'span', array( 'class' => 'error' ) );
		}
		echo '<select name="' . $property . '">';
		foreach( $keyTextArray as $key => $text ) {
			if( $value == $key ) {
				echo '<option value="' . $key . '" selected>' . $text . '</option>';
			}
			else {
				echo '<option value="' . $key . '">' . $text . '</option>';
			}
		}
		echo '</select>';
		Tags::close( 'span' );
	}

	protected function formRelationSelect( $property, $iterator, $nameProperty = 'title', $valueProperty = 'id' ) {
		$result = array();
		if( get_class( $iterator ) == 'Sql' ) {
			$iterator = $iterator->select();
		}
		while( $obj = $iterator->next() ) {
			$result[ $obj->get( $valueProperty ) ] = $obj->get( $nameProperty );
		}
		$this->formSelect( $property, $result );
	}

	protected function formTextarea( $property, $parameters = ''  ) {
		$this->registerField( $property );
		$value = $this->getObject()->get( $property );
		$value = str_replace( '\\\'', '\'', $value );
		if( $this->isError( $property ) ) {
			Tags::open( 'span', array( 'class' => 'error' ) );
		}
		echo '<textarea name="' . $property . '" ' . $parameters . ' id="' . $property . '" />' . $value . '</textarea>';
		Tags::close( 'span' );
	}

	protected function formUpload( $property, $parameters = '' ) {
		// Ako je forma samo s upload poljem, onda applikacija ne zna da ima ikoje polje (jer je
		// naziv upload polja obican, ane enkodiran). Zato treba ovo dodati:
		$this->formHidden( '_____Upload', 'true' );
		if( ! $this->multipartForm ) {
			echo '<h1>Use formStartMultipart() instead of formStart()</h1>';
		}
//		echo '<input type="file" name="' . $property . '" ' . $parameters . ' id="' . $property . ' " />';
		// Property nije zasticen jer ne moze zavrsiti greskom u bazi -- mora se s njime eksplicitno raditi
		if( $this->isError( $property ) ) {
			Tags::open( 'span', array( 'class' => 'error' ) );
		}
		echo '<input type="file" name="' . $property . '" ' . $parameters . ' id="' . $property . ' " />';
		Tags::close( 'span' );
	}

	protected function formSubmit( $text, $parameters = '' ) {
		$this->formSubmitNamed( 'Submit' . $text, $text, $parameters );
	}

	protected function formSubmitNamed( $name, $text, $parameters = '' ) {
		$this->registerField( $name );
		echo '<input name="' . $name . '" type="submit" value="' . $text . '" ' . $parameters . ' />';
	}

}