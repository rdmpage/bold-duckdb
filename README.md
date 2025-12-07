# DuckDB and barcode data

## Install DuckDB

[DuckDB](https://duckdb.org).

## Get data package

BOLD_Public.06-Sep-2024 as this is the one used to build BOLD-View:

https://bench.boldsystems.org/index.php/datapackage?id=BOLD_Public.06-Sep-2024

## Load data into DuckDB

Make a directory, start DuckDB with a database filename so it creates the database on disk.

```
cd ~/Develeopment
mkdir bold-duckdb
cd bold-duckdb

duckdb bold.duckdb

CREATE TABLE barcode AS
SELECT processid, sampleid, fieldid, museumid, record_id, specimenid, processid_minted_date, bin_uri, bin_created_date, collection_code, inst, sovereign_inst, taxid, kingdom, phylum, "class", "order", family, subfamily, tribe, genus, species, subspecies, species_reference, identification, identification_method, identification_rank, identified_by, identifier_email, taxonomy_notes, sex, reproduction, life_stage, short_note, notes, voucher_type, tissue_type, specimen_linkout, associated_specimens, associated_taxa, collectors, collection_date_start, collection_date_end, collection_event_id, collection_time, collection_notes, geoid, "country/ocean", country_iso, "province/state", region, sector, site, site_code, coord, coord_accuracy, coord_source, elev, elev_accuracy, depth, depth_accuracy, habitat, realm, biome, ecoregion, sampling_protocol, nuc_basecount, insdc_acs, funding_src, marker_code, primers_forward, primers_reverse, sequence_run_site, sequence_upload_date, bold_recordset_code_arr
FROM read_csv_auto('/Volumes/Acer/BOLD_Public.06-Sep-2024/BOLD_Public.06-Sep-2024.tsv', delim='\t');

```

This reads the TSV file and creates the table `barcode`. Note that the `CREATE TABLE` command excludes the `nuc` field as we don't want to load the sequences.

## Export data for specific columns

We want data from various columns to clean, map to identifiers, or convert to other formats such as RDF.

#### identified_by (JSON)

Names of people who identified specimens.

```
COPY (
  SELECT DISTINCT identified_by
  FROM barcode
  WHERE identified_by IS NOT NULL
) TO 'identified_by.json' (FORMAT JSON, ARRAY TRUE);
```

#### voucher_type (JSON)

Types of voucher (notionally a set of predefined terms, but in practice free form)

```
COPY (
  SELECT DISTINCT voucher_type
  FROM barcode
  WHERE identified_by IS NOT NULL
) TO 'voucher_type.json' (FORMAT JSON, ARRAY TRUE);
```

#### identification (TSV)

```
COPY (
    SELECT processid, identification
    FROM barcode
    WHERE identification IS NOT NULL
) TO 'identification.tsv' (FORMAT CSV, DELIMITER '\t', HEADER);
```

#### bold_recordset_code_arr (TSV)

```
COPY (
    SELECT processid, bold_recordset_code_arr
    FROM barcode
    WHERE bold_recordset_code_arr IS NOT NULL
) TO 'bold_recordset_code_arr.tsv' (FORMAT CSV, DELIMITER '\t', HEADER);
```

#### insdc_acs (TSV)

```
COPY (
    SELECT processid, insdc_acs
    FROM barcode
    WHERE insdc_acs IS NOT NULL
) TO 'insdc_acs.tsv' (FORMAT CSV, DELIMITER '\t', HEADER);
```


