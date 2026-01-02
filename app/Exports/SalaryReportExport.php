<?php

namespace App\Exports;

use Maatwebsite\Excel\Excel;

class SalaryReportExport
{
    protected $data;
    protected $title;
    protected $reportType;

    public function __construct($data, $title, $reportType = 'all')
    {
        $this->data = $data;
        $this->title = $title;
        $this->reportType = $reportType;
    }

    public function export()
    {
        \Excel::create($this->title, function($excel) {
            $excel->sheet('Salary Report', function($sheet) {
                // Add headers
                $headers = [
                    'Employee ID',
                    'Employee Name', 
                    'Department',
                    'Basic Salary',
                    'HRA',
                    'Conveyance',
                    'PF',
                    'PT',
                    'Gross Salary',
                    'Effective From'
                ];
                
                $sheet->row(1, $headers);
                
                // Add data rows
                $row = 2;
                foreach ($this->data as $item) {
                    $sheet->row($row, [
                        $item['emp_id'],
                        $item['employee_name'],
                        $item['department'],
                        $item['basic_salary'],
                        $item['hra'],
                        $item['conveyance_allowance'],
                        $item['pf'],
                        $item['pt'],
                        $item['gross_salary'],
                        $item['effective_from']
                    ]);
                    $row++;
                }
                
                // Style the header row
                $sheet->row(1, function($row) {
                    $row->setBackground('#232F3E');
                    $row->setFontColor('#FFFFFF');
                    $row->setFontWeight('bold');
                });
            });
        });
    }
}