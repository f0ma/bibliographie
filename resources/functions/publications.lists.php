<?php

$bibliographie_publication_types = array(
  'Article',
  'Book',
#  'Booklet',
#  'Inbook',
#  'Incollection',
  'Inproceedings',
#  'Manual',
#  'Masterthesis',
  'Misc',
  'Phdthesis',
#  'Proceedings',
#  'Techreport',
#  'Unpublished'
);

$bibliographie_publication_months = array(
  'January',
  'February',
  'March',
  'April',
  'May',
  'June',
  'July',
  'August',
  'September',
  'October',
  'November',
  'December'
);

$bibliographie_publication_fields = array(
  'article' => array(
    array(
      'author',
      'title',
      'journal',
      'year',
      'pages',
    ),
    array(
      'volume',
      'number',
      'month',
      'note'
    )
  ),
  'book' => array(
    array(
      'author,editor',
      'title',
      'publisher',
      'address',
      'year',
      'pages'
    ),
    array(
      'volume',
      'number',
      'series',
      'edition',
      'month',
      'note'
    )
  ),
  'booklet' => array(
    array(
      'title'
    ),
    array(
      'author',
      'howpublished',
      'address',
      'month',
      'year',
      'note'
    )
  ),
  'inbook' => array(
    array(
      'author,editor',
      'title',
      'chapter',
      'pages',
      'publisher',
      'type',
      'year'
    ),
    array(
      'volume',
      'number',
      'series',
      'address',
      'edition',
      'month',
      'note'
    )
  ),
  'incollection' => array(
    array(
      'author',
      'title',
      'booktitle',
      'publisher',
      'year'
    ),
    array(
      'editor',
      'volume',
      'number',
      'type',
      'series',
      'edition',
      'chapter',
      'pages',
      'address',
      'month',
      'note'
    )
  ),
  'inproceedings' => array(
    array(
      'author',
      'title',
      'booktitle',
      'year',
      'publisher',
      'address',
      'pages',
    ),
    array(
      'editor',
      'volume',
      'number',
      'organization',
      'series',
      'month',
      'note'
    )
  ),
  'manual' => array(
    array(
      'title'
    ),
    array(
      'author',
      'organization',
      'address',
      'edition',
      'month',
      'year',
      'note'
    )
  ),
  'masterthesis' => array(
    array(
      'author',
      'title',
      'school',
      'year',
      'address',
    ),
    array(
      'month',
      'note',
      'type'
    )
  ),
  'misc' => array(
    array(),
    array(
      'author',
      'title',
      'howpublished',
      'month',
      'year',
      'note'
    )
  ),
  'phdthesis' => array(
    array(
      'author',
      'title',
      'school',
      'year',
      'address',
      'pages',
    ),
    array(
      'month',
      'note',
    )
  ),
  'proceedings' => array(
    array(
      'title',
      'year'
    ),
    array(
      'editor',
      'publisher',
      'volume',
      'number',
      'organization',
      'series',
      'address',
      'month',
      'note'
    )
  ),
  'techreport' => array(
    array(
      'author',
      'title',
      'institution',
      'year'
    ),
    array(
      'type',
      'number',
      'address',
      'month',
      'note'
    )
  ),
  'unpublished' => array(
    array(
      'author',
      'title',
      'note'
    ),
    array(
      'month',
      'year'
    )
  )
);

$bibliographie_publication_data = array(
  'pub_type' => 'Publication type',
  'year' => 'Year',
  'month' => 'Month',
  'booktitle' => 'Booktitle',
  'chapter' => 'Chapter',
  'series' => 'Series',
  'journal' => 'Journal',
  'volume' => 'Volume',
  'number' => 'Number',
  'edition' => 'Edition',
  'publisher' => 'Publisher',
  'location' => 'Location',
  'howpublished' => 'How published',
  'organization' => 'Organization',
  'institution' => 'Institution',
  'school' => 'School',
  'address' => 'Address',
  'pages' => 'Pages',
  'note' => 'Note',
  'abstract' => 'Abstract',
  'userfields' => 'User fields',
  'bibtex_id' => 'BibTex ID',
  'isbn' => 'ISBN',
  'issn' => 'ISSN',
  'doi' => 'DOI',
  'url' => 'URL',
  'user_id' => 'Added by',
  'authors' => 'Authors',
  'editors' => 'Editors',
  'topics' => 'Topics',
  'tags' => 'Tags'
);