@rem Usage: bom_jlc.php check [file.csv] [jlc_part_number_column_name] [bom_part_number_column_name] [bom_package_column_name]

@php -q bom_jlc.php check test-bom.csv "Supplier Part Number 3" "Comment" "PackageReference" > test-bom-get-description.txt
