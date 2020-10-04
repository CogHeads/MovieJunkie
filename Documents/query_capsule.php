<?php
    class Field_Rel implements ArrayAccess {
        private array $_Fields;

        function __construct(string... $many_fields)
        {
            $this->_Fields = $many_fields;
        }

        public function Independent() : Field_Rel
        {
            $copy = new Field_Rel(...$this->Data());
            foreach ($copy as $key => $value)
                $copy[$key] = substr($value, strpos($value, ".") + 1);
            
            return $copy;
        }

        public function Under(string $Parent): Field_Rel
        {
            $copy = new Field_Rel(...$this->Data());
            foreach ($copy->Data() as $key => $value)
                $copy[$key] = $Parent . "." . $value;     
            return $copy;
        }

        public function __toString(){
            return implode(",", $this->Data());
        }

        public function Data(): array
        {
            return $this->_Fields;
        }

        public function offsetSet($offset, $value)
        {
            if(is_null($offset))
                $this->_Fields[] = $value;
            else
                $this->_Fields[$offset] = $value;
        }

        public function offsetExists($offset)
        {
            return isset($this->_Fields[$offset]);
        }

        public function offsetUnset($offset)
        {
            unset($this->_Fields[$offset]);
        }

        public function offsetGet($offset)
        {
            return isset($this->_Fields[$offset]) ? $this->_Fields[$offset] : null;
        }
    }

    class Table_Field_Rel{
        private string $_Table;
        private Field_Rel $_Table_Fields;

        function __construct(string $table_name, string ...$table_fields)
        {
            $this->_Table = $table_name;
            $this->_Table_Fields = new Field_Rel(...$table_fields);
        }

        public function Table(): string
        {
            return $this->_Table;
        }
       
        public function GobalField(int $index = 0): string
        {
            return $this->Table() . '.' . $this->Field($index);
        }
       
        public function GlobalFields(): Field_Rel
        {
            $array = $this->Fields()->Data();
            foreach($array as $key => $value)
                $array[$key] = $this->Table() . '.' . $value;
            
            return new Field_Rel(...$array);
        }

        public function Field(int $index = 0): string
        {
            return $this->Fields()->Data()[$index];
        }

        public function Fields(): Field_Rel
        {
            return $this->_Table_Fields;
        }
    }

    class Query_Capsule{
        function __construct(Table_Field_Rel ...$TFRs)
        {
            foreach($TFRs as $TFR){
                $this->_Tables[] = $TFR->Table();
                foreach($TFR->GlobalFields()->Data() as $selection)
                    $this->_Selections[] = $selection;

                $this->_Conditions = "";
                $this->_Groupings = "";
                $this->_Aggregate_Conditions = "";
                $this->_Orderings = "";
                $this->_Joins = "";
            }
        }

        public function Selection(int $index = 0): string
        {
            return $this->_Selections[$index];
        }

        public function Selections(): array
        {
            return $this->_Selections;
        }

        public function SelectionsFrom(string $Table_name): array
        {
            $Subset = [];

            $selections = $this->Selections();
            $size = count($selections);
            for($index = 0; $index < $size; ++$index)
            {
                $fragments = explode(".", $selections[$index]); 
                
                if($fragments[0] == $Table_name){
                    do {
                        $Subset[] = $fragments[1];
                        $fragments = explode(".", $selections[++$index]);
                    } while ($fragments[0] == $Table_name);

                    break;
                }    
            }

            return $Subset;
        }

        public function Table(int $index = 0): string
        {
            return $this->_Tables[$index];
        }

        public function Tables(): array
        {
            return $this->_Tables;
        }

        function __toString()
        {
            return "{$this->SelectFrom()} {$this->Join()} {$this->Where()} {$this->GroupBy()} {$this->Having()} {$this->OrderBy()}";
        }

        public function Table_Selection_Map(): array
        {
            $TS_Set = [];

            foreach($this->Tables() as $table)
                $TS_Set[$table] = new Field_Rel(...$this->SelectionsFrom($table));

            return $TS_Set;
        }

        public function SelectFrom(): string
        {
            if($this->_Selections)
                return "SELECT " . implode(",", $this->Selections()) . " FROM " . implode(",", $this->Tables());
            return "SELECT * FROM " . implode(",", $this->Tables());
        }

        public function Join(): string
        {
            return $this->_Joins;
        }

        public function Where(): string
        {
            if($this->_Conditions)
                return "WHERE ({$this->_Conditions})";
            return "";
        }

        public function GroupBy(): string
        {
            if($this->_Groupings)
                return "GROUP BY ({$this->_Groupings})";
            return "";
        }

        public function Having(): string
        {
            if($this->_Aggregate_Conditions)
                return "HAVING ({$this->_Aggregate_Conditions})";
            return "";
        }

        public function OrderBy(): string
        {
            if($this->_Orderings)
                return "ORDER BY ({$this->_Orderings})";
            return "";
        }

        public function SetJoin(string $join_type, string $table_name, string $condition = "", string $select_format = ""): void
        {
            $table_name = $this->Translate($table_name, $select_format);

            if($condition){
                $condition = $this->Translate($condition, $select_format);
                $this->_Joins = "{$join_type} JOIN {$table_name} ON {$condition}";
            }
            else
                $this->_Joins = "{$join_type} JOIN {$table_name}";
        }

        public function SetWhere(string $encoded, string $select_format = ""): void
        {
            $this->_Conditions = $this->Translate($encoded, $select_format);
        }

        public function SetGrouping(string $encoded, string $select_format = ""): void
        {
            $this->_Groupings = $this->Translate($encoded, $select_format);
        }

        public function SetHaving(string $encoded, string $select_format = ""): void
        {
            $this->_Aggregate_Conditions = $this->Translate($encoded, $select_format);
        }

        public function SetOrdering(string $encoded, string $select_format = ""): void
        {
            $this->_Orderings = $this->Translate($encoded, $select_format);
        }

        public function SelectFromQuery(string $selections, string $select_format = ""): string
        {
            $selections = $this->Translate($selections, $select_format);
            $tables = implode(",", $this->Tables());
            return "SELECT {$selections} FROM {$tables}";
        }

        public function JoinQuery(string $join_type, string $table_name, string $condition = "", string $select_format = ""): string
        {
            $table_name = $this->Translate($table_name, $select_format);

            if(!$condition)
                return  "{$join_type} JOIN {$table_name}";
            
            $condition = $this->Translate($condition, $select_format);
            return "{$join_type} JOIN {$table_name} ON {$condition}";
        }

        public function WhereQuery(string $condition, string $select_format = ""): string
        {
           $condition = $this->Translate($condition, $select_format);
            return "WHERE ({$condition})";
        } 

        public function InsertValuesQuery(string $values, string $fields = "", int $table_index = 0, $select_format = ""): string
        {
            $table_name = $this->Table($table_index);
            $selections = [];

            if($fields){
                $fields = $this->Translate($fields, $select_format);
                $selections = explode(",", $fields);

                foreach ($selections as $key => $selection)
                    $selections[$key] = trim($selection);
            }
            else
                $selections = $this->SelectionsFrom($table_name);

            $selections = new Field_Rel(...$selections);
            $values = $this->Translate($values, $select_format);

            return "INSERT INTO {$table_name} ({$selections}) VALUES ({$values})";
        }

        public function UpdateQuery(string $settings, string $condition = "", int $table_index = 0, $select_format = ""): string
        {
            $settings = $this->Translate($settings, $select_format);
            $condition = $condition ? $this->Translate($condition, $select_format) : "true";
            $table_name = $this->Table($table_index);
            $reference_table = $table_name . ' ' . $this->Join();

            return "UPDATE {$table_name} SET {$settings} FROM {$reference_table} WHERE {$condition}";
        }

        public function DeleteQuery(string $tables, string $condition = "", string $select_format = ""): string
        {
            $condition = $this->Translate($condition, $select_format);
            $reference_table = $this->Join();
            return "DELETE {$tables} FROM {$reference_table} WHERE {$condition}";
        }

        private function Translate(string $encoded, string $reference): string
        {
            return $this->Parse_All($this->FormatShortHand($encoded, $reference));
        }

        private function FormatShortHand(string $canvas, string $format): string //formats from placeholder tags [${number}_] to encoded form [$(table_no).(field_no)]
        {
            if($format){
                $pieces = explode(",", $format);
                foreach($pieces as $key => $piece)
                    $pieces[$key] = trim($piece);

                $limit = count($pieces);
                for($var = 0; $var < $limit; ++$var)
                    $canvas = str_replace('$' . $var . "_", '$' . $pieces[$var], $canvas);
            }

            return $canvas;
        }

        private function Parse_All(string $encoded): string //converts fields and tables from a Global Reference
        {
            $Table_Field_Map = $this->Table_Selection_Map();
            $Table_Names = $this->Tables();

            $substitute_fields = function (array $matches) use($Table_Field_Map, $Table_Names): string
            {
                $table_name = $Table_Names[$matches[2]];
                $fields = $Table_Field_Map[$table_name]->Under($table_name);
                return $matches[1] . $fields[$matches[3]];
            };

            $substitute_fields_front = function (array $matches) use($Table_Field_Map, $Table_Names): string
            {
                $table_name = $Table_Names[$matches[1]];
                $fields = $Table_Field_Map[$table_name]->Under($table_name);
                return $fields[$matches[2]];
            };
           
            $substitute_tables = function (array $matches) use($Table_Names): string
            {
                return $matches[1] . $Table_Names[$matches[2]];
            };
            
            $substitute_tables_front = function (array $matches) use($Table_Names): string
            {
                return $Table_Names[$matches[1]];
            };

            while(1){
                $advancement = preg_replace_callback("/" . "([^A-Za-z0-9\_])[\$]([0-9]+)[\.]([0-9]+)" . "/", $substitute_fields, $encoded);
                $advancement = preg_replace_callback("/" . "^[\$]([0-9]+)[\.]([0-9]+)" . "/", $substitute_fields_front, $advancement);
                if($encoded == $advancement)
                    break;
                $encoded = $advancement;
            }
            
            while(1){
                $advancement = preg_replace_callback("/" . "([^A-Za-z0-9\_])[\$]([0-9]+)" . "/", $substitute_tables, $encoded);
                $advancement = preg_replace_callback("/" . "^[\$]([0-9]+)" . "/", $substitute_tables_front, $advancement);
                if($encoded == $advancement)
                    break;
                $encoded = $advancement;
            }

            return $encoded;
        }
        
        private function Parse_Fields(string $encoded): string //converts fields from a Local Reference
        {
            $Table_Field_Map = $this->Table_Selection_Map();
            $Table_Names = $this->Tables();

            $substitute_fields = function (array $matches) use($Table_Field_Map, $Table_Names): string
            {
                $table_name = $Table_Names[$matches[2]];
                $fields = $Table_Field_Map[$table_name];
                return $matches[1] . $fields[$matches[3]];
            };
            
            $substitute_fields_front = function (array $matches) use($Table_Field_Map, $Table_Names): string
            {
                $table_name = $Table_Names[$matches[1]];
                $fields = $Table_Field_Map[$table_name];
                return $fields[$matches[2]];
            };

            while(1){
                $advancement = preg_replace_callback("/" . "[^A-Za-z0-9\_][\$]([0-9]+)[\.]([0-9]+)" . "/", $substitute_fields, $encoded);
                $advancement = preg_replace_callback("/" . "^[\$]([0-9]+)[\.]([0-9]+)" . "/", $substitute_fields_front, $advancement);
                if($encoded == $advancement)
                    break;
                $encoded = $advancement;
            }

            return $encoded;
        }
        
        private function Parse_Tables(string $encoded): string //converts tables from a Global Reference
        {
            $Table_Names = $this->Tables();

            $substitute_tables = function (array $matches) use($Table_Names): string
            {
                return $matches[1] . $Table_Names[$matches[2]];
            };
           
            $substitute_tables_front = function (array $matches) use($Table_Names): string
            {
                return $Table_Names[$matches[1]];
            };
        
            while(1){
                $advancement = preg_replace_callback("/" . "[^A-Za-z0-9\_][\$]([0-9]+)" . "/", $substitute_tables, $encoded);
                $advancement = preg_replace_callback("/" . "^[\$]([0-9]+)" . "/", $substitute_tables_front, $advancement);
                if($encoded == $advancement)
                    break;
                $encoded = $advancement;
            }

            return $encoded;
        }

        private array $_Selections;
        private array $_Tables;
        private string $_Joins;
        private string $_Conditions;
        private string $_Groupings;
        private string $_Aggregate_Conditions;
        private string $_Orderings;
    }
?>
