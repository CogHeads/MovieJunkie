<?php
    function Init()
    {
        require_once("query_capsule.php");
        require_once("database_relay.php");

        define('DB_USER', 'user');
        define('DB_PASSWORD', 'password');
        define('DB_HOST', 'localhost');
        define('DB_NAME', 'database');

        //All possible selections
        $searchable_tables = [
            new Table_Field_Rel(
                "User",
                    "name",
                    "user_id"
            ),
            new Table_Field_Rel(
                "Movies",
                    "movie_name",
                    "year_of_release",
                    "ratings",
                    "movie_id"
            ),
            new Table_Field_Rel(
                "Crew",
                    "crew_name",
                    "movie_id"
            )
        ];

        //Tables segregation flags
        $include_table = [
            $_GET["enable-user"],
            $_GET["enable-movie"],
            $_GET["enable-crew"]
        ];

        $searching_tables = []; //Tables being queried
        $searching_conditions = []; //Where Clause Container
        $search_ordering = []; //Order By Clause Container

        if($include_table[0]) //If including users, compare UserID with search term
            $searching_conditions[] = "($0.1 like '$0_')";
        
        if($include_table[1]) //If including movies, order by Year Of Release and Rating
        {
            if($filter_YOR = $_GET["filter-YOR"])
                $search_ordering[] = $filter_YOR > 0 ? "Movies.year_of_release DESC" : "Movies.year_of_release ASC";
            
            if($filter_rating = $_GET["filter-rating"])
                $search_ordering[] = $filter_rating > 0 ? "Movies.ratings DESC" : "Movies.ratings ASC";
        }

        $index = 0;
        foreach($searchable_tables as $key => $searchable_table)
            if($include_table[$key]) //Include Table in the Query if flagged
            {
                $searching_tables[] = $searchable_table;
                $searching_conditions[] = "(\${$index}.0 like '$0_')";
                $search_ordering[] = "\${$index}.0 ASC";
                ++$index;
            }

        $search_query = new MySQL_Query_Capsule(...$searching_tables); //Creating Query Container
        $searching_conditions = "(" . implode(" OR ", $searching_conditions) . ")";
        $search_ordering = implode(",", $search_ordering);
        
        //Table-specific conditioning[ Year Of Release, Rating ]
        if($include_table[1])
        {
            $lower = [];
            $upper = [];
            
            $field_numbers = [];

            if($_POST['filter-YOR']){
                $lower[] = $_GET["date-from"];
                $upper[] = $_GET["date-to"];
                $field_number[] = 1;
            }
            
            if($_GET['filter-rating']){
                $lower[] = $_GET["rating-from"];
                $upper[] = $_GET["rating-to"];
                $field_number[] = 2;
            }
            
            foreach($field_numbers as $fn){
                $index = $fn - 1;
                $searching_conditions .= " AND ($1.{$fn} BETWEEN {$lower[$index]} AND {$upper[$index]})";
            }
        }

        $pattern = $_GET["search-term"];

        if($search_query->isAssigned()){
            $DB_Manager = new DB_Relay(
                DB_HOST,
                DB_USER,
                DB_PASSWORD,
                DB_NAME
            );

            $search_query->
            SetGrouping($search_ordering);

            //search for exact matches
            $search_query->
            SetWhere(
                $searching_conditions,
                "{$pattern}"
            );
            
            $DB_Manager->PushQuery($search_query);
            
            //search for 'starting with'
            $search_query->SetWhere(
                $searching_conditions,
                "{$pattern}%"
            );
            
            $DB_Manager->PushQuery($search_query);
            
            //search for 'ending with'
            $search_query->SetWhere(
                $searching_conditions,
                "%{$pattern}"
            );
            
            $DB_Manager->PushQuery($search_query);
            
            //search for 'contains'
            $search_query->SetWhere(
                $searching_conditions,
                "%{$pattern}%"
            );
            
            $DB_Manager->PushQuery($search_query);

            while ($query = $DB_Manager->PopQuery())
                echo $query . "\n";

            $DB_Manager->FlushStack();
        }
    }

    Init();
?>
