<?php
    class DB_Relay
    {
        function __construct($host = null, $user = null, $password = null, $database = null, $port = null, $socket = null)
        {
            $this->_db_link = @mysqli_connect(
                $host,
                $user,
                $password,
                $database,
                $port,
                $socket
            )
                OR die('MySQL connection failed: ' . mysqli_connect_error());
        
            $this->_query_stack = "";
        }

        function __destruct()
        {
            @mysqli_close($this->_db_link);
        }

        public function RelayQuery(string $query): mysqli_result
        {
            return @mysqli_query($this->_db_link, $this->CleanQuery($query));
        }
        
        public function RelayStack(): mysqli_result
        {
            return @mysqli_query($this->_db_link, $this->_query_stack);
        }

        public function PrepareQuery(string $query): mysqli_stmt
        {
            return @mysqli_prepare($this->_db_link, $this->CleanQuery($query));
        }

        public function PrepareStack(): mysqli_stmt
        {
            return @mysqli_prepare($this->_db_link, $this->_query_stack);
        }

        public function PushQuery(string $query): void
        {
            $this->_query_stack .= $this->CleanQuery($query);
        }

        public function PopQuery(): string
        {
            $popped = "";

            if($this->isEmptyStack()){
                $start = strrpos($this->_query_stack, ";", -2);

                if($start !== false)
                    ++$start;
            
                $popped = substr($this->_query_stack, $start);
                $this->_query_stack = substr($this->_query_stack,0, -strlen($popped));
            }

            return $popped;
        }
      
        public function EmptyStack(): void
        {
            $this->_query_stack = "";
        }
        
        public function FlushStack(): void
        {
            $this->RelayStack();
            $this->EmptyStack();
        }

        public function isEmptyStack(): bool
        {
            return $this->_query_stack == "";
        }
        
        public function GetDatabaseLink(): mysqli
        {
            return $this->_db_link;
        }

        private function CleanQuery($query): string
        {
            return preg_replace("/[\s]{2,}/", " ", rtrim(trim($query, " \t\n\r\0\x0B"), "\;") . ";");
        }

        private mysqli $_db_link;
        private string $_query_stack;
    }
?>
