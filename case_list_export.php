<?php
include "db_connect.php";
$obj = new DB_connect();

// Query to fetch the required data
$query = "
    SELECT 
        c.date_of_filing,
        c.case_no,
        c.applicant,
        c.opp_name,
        c.complainant_advocate,
        c.respondent_advocate,
        s.name AS complainant,
        cp.next_date,
        cp.remarks AS proceeding_remarks,
        st.stage AS next_stage,
        cmp.name AS company_name,
        cst.case_type AS case_type_name,
        ct.name AS city_name,
        crt.name AS court_name
    FROM 
        `case` AS c
    LEFT JOIN 
        staff AS s ON s.id = c.handle_by AND s.type = 'advocate'
    LEFT JOIN 
        case_procedings AS cp ON cp.case_id = c.id 
        AND cp.date_of_creation = (
            SELECT MAX(date_of_creation)
            FROM case_procedings
            WHERE case_id = c.id
        )
    LEFT JOIN 
        stage AS st ON st.id = cp.next_stage
    LEFT JOIN 
        company AS cmp ON cmp.id = c.company_id
    LEFT JOIN 
        case_type AS cst ON cst.id = c.case_type
    LEFT JOIN 
        city AS ct ON ct.id = c.city_id
    LEFT JOIN 
        court AS crt ON crt.id = c.court_name
    ORDER BY 
        c.id DESC
";

$stmt_list = $obj->con1->prepare($query);
$stmt_list->execute();
$result = $stmt_list->get_result();
$stmt_list->close();

// Output CSV filename
$filename = "Case_List.csv";

// Open file pointer to memory
$f = fopen('php://memory', 'w');

// Define column headers for CSV
$fields = array(
    'Date of Filing',
    'Case Number',
    'Complainant',
    'Complainant Advocate',
    'Opponent',
    'Opponent Advocate',
    'Next Stage',
    'Court',
    'Proceeding Remarks',
    'Next Date',
    'Company Name',
    'Case Type',
    'City Name'
);
$delimiter = ",";

// Write headers to the CSV file
fputcsv($f, $fields, $delimiter);

// Write data rows to the CSV file
while ($row0 = mysqli_fetch_assoc($result)) {
    $lineData = array(
        $row0['date_of_filing'],
        $row0['case_no'],
        $row0['complainant'],
        $row0['complainant_advocate'],
        $row0['opp_name'],
        $row0['respondent_advocate'],
        $row0['next_stage'],
        $row0['court_name'],
        $row0['proceeding_remarks'],
        $row0['next_date'],
        $row0['company_name'],
        $row0['case_type_name'],
        $row0['city_name']
    );
    fputcsv($f, $lineData, $delimiter);
}

// Reset file pointer to the beginning
fseek($f, 0);

// Set headers to prompt a file download
header("Content-Disposition: attachment; filename=\"$filename\"");
header("Content-Type: application/csv");

// Output all data in memory to the file
fpassthru($f);

?>