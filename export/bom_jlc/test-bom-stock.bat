@rem Usage: bom_jlc.php stock [file.csv] [jlc_part_number_column_name] [designator_column_name] [num_boards] [add_cost_of_manufacturing] [add_cost_of_each_exp_parttype]

@php -q bom_jlc.php stock test-bom.csv "Supplier Part Number 3" "Designator" 5 9.00 3.00 > test-bom-stock.txt
