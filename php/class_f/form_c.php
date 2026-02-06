<?php
class form_c {

	private $pdo;
	private $do_;
	public $debug = false;

	public function __construct($pdo, $do_) {
		$this->pdo = $pdo;
		$this->do_ = $do_;
	}
	
	public function label($yazi, $attr = []){
		$attrs = '';
		foreach ($attr as $key => $value) {
			$attrs .= ' '.$key.'="'.$value.'"';
		}
		return '<label'.$attrs.'>'.$yazi.'</label>';
	}

	public function input_text($value, $attr = []) {
		$attrs = '';
		$type = isset($attr['type']) ? $attr['type'] : 'text';
		unset($attr['type']);
		
		foreach ($attr as $key => $val) {
			if ($val === null) {
				continue;
			}
			$attrs .= ' '.$key.'="'.$val.'"';
		}
		return '<input type="'.$type.'" value="'.$value.'"'.$attrs.' />';
	}

	public function label_input_text($yazi, $attr = [], $label_options = ['text' => '', 'class' => '']) {
		$label_text = $this->label($label_options['text'] ?? '', $label_options['class'] ?? '');
        $input_text = $this->input_text($yazi, $attr);
        return $label_text . $input_text;
	}

	public function textarea($value, $attr = []) {
		$attrs = '';
		foreach ($attr as $key => $val) {
			$attrs .= ' '.$key.'="'.$val.'"';
		}
		return '<textarea'.$attrs.'>'.$value.'</textarea>';
	}

	public function label_textarea($yazi, $attr = [], $label_options = ['text' => '', 'class' => '']) {
		$label_text = $this->label($label_options['text'] ?? '', $label_options['class'] ?? '');
		$textarea = $this->textarea($yazi, $attr);
		return $label_text . $textarea;
	}

	public function range3_radio($name, $labels = ['1' => '1', '2' => '2', '3' => '3'], $checkedValue = null, $attr = []) {
		$attrs = '';
		foreach ($attr as $key => $val) {
			$attrs .= ' '.$key.'="'.$val.'"';
		}
		$html = '<div class="range3"'.$attrs.'>' . PHP_EOL;
		foreach ($labels as $value => $label) {
			$id = htmlspecialchars($name . '-' . $value, ENT_QUOTES, 'UTF-8');
			$isChecked = ($value == $checkedValue) ? ' checked' : '';
			$html .= '    <input type="radio" name="'.htmlspecialchars($name).'" id="'.$id.'" class="range3-input" value="'.htmlspecialchars($value).'"'.$isChecked.' />' . PHP_EOL;
		}
		// Labellar
		foreach ($labels as $value => $label) {
			$id = htmlspecialchars($name . '-' . $value, ENT_QUOTES, 'UTF-8');
			$labelText = htmlspecialchars($label, ENT_QUOTES, 'UTF-8');
			$html .= '    <label for="'.$id.'" class="range3-label">'.$labelText.'</label>' . PHP_EOL;
		}
		// Slider
		$html .= '    <div class="range3-slider">' . PHP_EOL;
		$html .= '        <div class="range3-marker"></div>' . PHP_EOL;
		$html .= '    </div>' . PHP_EOL;
		$html .= '</div>' . PHP_EOL;
	
		return $html;
	}
	
	public function input_radio($value, $options = ['select_adi' => 'adi', 'from_tablo' => '', 'db_ek' => "", 'label_class' => 'radio-label'], $attr = []) {
		$attrs = '';
		foreach ($attr as $key => $val) {
			$attrs .= ' '.$key.'="'.$val.'"';
		}
	
		$select_adi = $options['select_adi'] ?? 'adi';
		$from_tablo = $this->do_ . ($options['from_tablo'] ?? '');
		$db_ek_ = $options['db_ek'] ?? "";
		$label_class = $options['label_class'] ?? 'radio-label';
		$name = $attr['name'] ?? '';
	
		$sonuc = '';
		$sorgu_al_ = $this->pdo->prepare("SELECT no, $select_adi FROM $from_tablo WHERE no>0 $db_ek_");
		$sorgu_al_->execute();
		$results = $sorgu_al_->fetchAll(PDO::FETCH_OBJ);
	
		foreach ($results as $result) {
			$checked = ($value == $result->no) ? ' checked="checked"' : '';
			$input = '<input type="radio" value="'.$result->no.'"'.$attrs.$checked.' />';
			
			if (strpos($select_adi, ",") !== false) {
				$label_text = [];
				$select_adi_dizi = explode(",", $select_adi);
				foreach ($select_adi_dizi as $s) {
					$s = trim($s);
					$label_text[] = c($result->{$s});
				}
				$label_text = implode(" ", $label_text);
			} else {
				$label_text = c($result->{$select_adi});
			}
			
			$sonuc .= '<label class="'.$label_class.'">'.$input.$label_text.'</label>';
		}
		
		return $sonuc;
	}
	
	public function input_checkbox($value, $options = ['select_adi' => 'adi', 'from_tablo' => '', 'db_ek' => "", 'label_class' => 'checkbox-label'], $attr = []) {
		$attrs = '';
		foreach ($attr as $key => $val) {
			$attrs .= ' '.$key.'="'.$val.'"';
		}
	
			$select_adi = $options['select_adi'] ?? 'adi';
			$from_tablo = $this->do_ . ($options['from_tablo'] ?? '');
			$db_ek_ = $options['db_ek'] ?? "";
			$label_class = $options['label_class'] ?? 'checkbox-label';
			$name = $attr['name'] ?? '';
		
			$sonuc = '';
			$sorgu_al_ = $this->pdo->prepare("SELECT no, $select_adi FROM $from_tablo WHERE no>0 $db_ek_");
			$sorgu_al_->execute();
			$results = $sorgu_al_->fetchAll(PDO::FETCH_OBJ);
		
			foreach ($results as $result) {
				$checked = (is_array($value) && in_array($result->no, $value)) ? ' checked="checked"' : '';
				$input = '<input type="checkbox" value="'.$result->no.'"'.$attrs.$checked.' />';
				
				if (strpos($select_adi, ",") !== false) {
					$label_text = [];
					$select_adi_dizi = explode(",", $select_adi);
					foreach ($select_adi_dizi as $s) {
						$s = trim($s);
							$label_text[] = c($result->{$s});
					}
					$label_text = implode(" ", $label_text);
				} else {
					$label_text = c($result->{$select_adi});
				}
				
				$sonuc .= '<label class="'.$label_class.'">'.$input.$label_text.'</label>';
			}
			
			return $sonuc;
		}

	public function select( $first_option, 
							$options = ['ajax' => 0, 'select_adi' => 'adi', 'esitlik_deger_no' => '', 'from_tablo' => '', 'db_ek' => "", 'value_column' => 'no'], 
							$attr = []) {
		$ajax = $options['ajax'] ?? 0;

		if (is_array($first_option)) {
			if (!empty($first_option)) {
				$value = $first_option[0] ?? 0;
				$text = $first_option[1] ?? '';
				$first_option = "<option value=\"{$value}\">{$text}</option>";
			} else {
				$first_option = '';
			}
		} else {
			$first_option = strpos($first_option, '<option') !== false ? $first_option : '';
		}
	
		$attrs = '';
		foreach ($attr as $key => $val) {
			$attrs .= ' '.$key.'="'.$val.'"';
		}

		$select_adi = $options['select_adi'] ?? 'adi';
		$esitlik_deger_no = $options['esitlik_deger_no'] ?? '';
		$from_tablo = $this->do_ . ($options['from_tablo'] ?? '');
		$db_ek_ = $options['db_ek'] ?? "";
		$value_column = $options['value_column'] ?? 'no';
		$name = $attr['name'] ?? '';
		
		if ($ajax) {
			if (intval($esitlik_deger_no) > 0 ) {
				$select_stmt = $this->pdo->prepare("SELECT no, $select_adi FROM $from_tablo WHERE no = $esitlik_deger_no");
				$select_stmt->execute();
				$select_b = $select_stmt->fetch();
				$value = $select_b->no;
				$text = $select_b->$select_adi;
				$first_option = "<option value=\"{$value}\">{$text}</option>";
			}
			return '<select'.$attrs.'>'.$first_option.'</select>';

		} else {

			$sonuc = '';
			$sorgu_al_= $this->pdo->prepare("SELECT $value_column, $select_adi FROM $from_tablo WHERE no>0 $db_ek_");
			$sorgu_al_->execute();
			$results = $sorgu_al_->fetchAll(PDO::FETCH_OBJ);
			$sonuc .= '<select'.$attrs.'>';
			$sonuc .= $first_option != '' ? $first_option : '';

			foreach ($results as $result) {
				$value_field = $value_column;
				
				$sonuc .= '<option value="'.$result->$value_field.'"';
				if ($esitlik_deger_no == 'get') {
					$get_name = isset($_GET[$name]) ? z($_GET[$name]) : false;
					$sonuc .= ($get_name != false && $get_name == $result->$value_field) ? ' selected="selected"' : '';
				} else {
					$sonuc .= ($esitlik_deger_no == $result->$value_field) ? ' selected="selected"' : '';
				}
				$sonuc .= '>';

				if (strpos($select_adi, ",") !== false) {
					$select_adi_yazi = [];
					$select_adi_dizi = explode(",", $select_adi);
					foreach ($select_adi_dizi as $s) {
						$s = trim($s);
						$select_adi_yazi[] = c($result->{$s});
					}
					$select_adi_yazi = implode(" ", $select_adi_yazi);
					$sonuc .= str_replace("_", " ", ucfirst(c(substr($select_adi_yazi, 0, 40))));
				} else {
					$sonuc .= c(substr($result->{$select_adi}, 0, 40));
				}
			}
			$sonuc .= '</option>';
			
			$sonuc .= '</select>';
			return $sonuc;
		}
	}

	public function input_hidden($value, $attr = []) {
		$attrs = '';
		foreach ($attr as $key => $val) {
			$attrs .= ' '.$key.'="'.$val.'"';
		}
		return '<input type="hidden" value="'.$value.'"'.$attrs.' />';
	}

}

?>