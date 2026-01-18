# DuckDB and barcode data

## Install DuckDB

[DuckDB](https://duckdb.org).

## Get data package

At the start I use BOLD_Public.06-Sep-2024 as this is the one used to build the BOLD-View app. Get the data from https://bench.boldsystems.org/index.php/datapackage?id=BOLD_Public.06-Sep-2024

## Load data into DuckDB

In this folder start DuckDB with a database filename so it creates the database on disk, then import from TSV, specifying the columns you want.

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

Note that BOLD uses the Barcode Core Data Model (BCDM) model, this model is discussed at [DNAdiversity/BCDM](https://github.com/DNAdiversity/BCDM), which also has a mapping between BCDM and Darwin Core.

### Examples of export and/or analysis of columns

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

Types of voucher (notionally a set of predefined terms, but in practice lots of free-form entries).

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

#### Geocoordinates that are not country centroids

BOLD has coordinates that are the country’s centroid, not the actual sample location. These can dramatically misplace the barcoded specimen (and invalidate any “ecoregion” they may be placed in).

For the BOLD_Public.06-Sep-2024 data package:

```
SELECT COUNT(processid) FROM barcode WHERE coord_source = 'Coordinates from country centroid';
┌──────────────────┐
│ count(processid) │
│      int64       │
├──────────────────┤
│      508146      │
└──────────────────┘
```

Hence 0.5M geocoordinates are for the country, not the actual locality.

To select geocoordinates that aren’t centroids, together with additional geographic information:

```
COPY (
   SELECT processid, "country/ocean", country_iso, "province/state", region, sector, site, coord
   FROM barcode
   WHERE coord IS NOT NULL AND coord != 'None' AND coord_source != 'Coordinates from country centroid'
) TO 'coord.tsv' (FORMAT CSV, DELIMITER '\t', HEADER);
```

#### Distinct coords

Could use geohash as identifiers (e.g., https://geohash.softeng.co), also consider encoding using H3 (at a series of resolutions) https://h3geo.org/docs/comparisons/geohash/.

```
SELECT DISTINCT "country/ocean", country_iso, "province/state", region, sector, site, coord
   FROM barcode
   WHERE coord IS NOT NULL AND coord != 'None'
      AND coord_source != 'Coordinates from country centroid';
```

#### Get localities for a country

```
SELECT processid, "country/ocean", country_iso, "province/state", region, sector, site, coord
   FROM barcode
   WHERE country_iso = 'AU';
```


#### Sampling methods

```
COPY (
   SELECT DISTINCT sampling_protocol
   FROM barcode
   WHERE sampling_protocol IS NOT NULL AND sampling_protocol != 'None'
) TO 'sampling_protocol.tsv' (FORMAT CSV, DELIMITER '\t', HEADER);
```

#### Vouchers, taxonomic notes, etc.


```
COPY (
    SELECT processid, notes
    FROM barcode
    WHERE notes NOT NULL and notes != 'None'
) TO 'notes.tsv' (FORMAT CSV, DELIMITER '\t', HEADER);
```

```
COPY (
    SELECT processid, taxonomy_notes
    FROM barcode
    WHERE taxonomy_notes NOT NULL and taxonomy_notes != 'None'
) TO 'taxonomy_notes' (FORMAT CSV, DELIMITER '\t', HEADER);
```

COPY (
    SELECT processid, voucher_type
    FROM barcode
    WHERE voucher_type NOT NULL and voucher_type != 'None'
) TO 'voucher_type' (FORMAT CSV, DELIMITER '\t', HEADER);



