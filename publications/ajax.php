<?php
define('BIBLIOGRAPHIE_OUTPUT_BODY', false);

require '../init.php';

$title = 'An error occured!';
$text = 'An error occurred...';
switch ($_GET['task']) {
	case 'deleteAttachmentConfirm':
		$attachment = bibliographie_attachments_get_data($_GET['att_id']);

		if (is_object($attachment)) {
			$text = 'You are about to delete the following attachment.'
				. '<p>' . bibliographie_attachments_parse($attachment->att_id) . '</p>'
				. 'If you are sure, click "delete" below!'
				. '<p class="success"><a href="' . BIBLIOGRAPHIE_WEB_ROOT . '/publications/?task=deleteAttachment&amp;att_id=' . ((int) $attachment->att_id) . '">' . bibliographie_icon_get('delete') . ' Delete!</a></p>'
				. 'If you don\'t want to delete the attachment, press "cancel" below!';
		}

		bibliographie_dialog_create('deleteAttachmentConfirm_' . ((int) $_GET['att_id']), 'Confirm delete', $text);
		break;

	case 'registerAttachment':
		$register = bibliographie_attachments_register($_GET['pub_id'], $_GET['name'], $_GET['location'], $_GET['type']);
		if (is_array($register)) {
			echo bibliographie_attachments_parse($register['att_id']);
		}else
			echo '<p class="error">An error occurred!</p>';
		break;

	case 'uploadAttachment':
		$upload_handler = new UploadHandler(
				array(
					'upload_dir' => BIBLIOGRAPHIE_ROOT_PATH . '/attachments/',
					'script_url' => BIBLIOGRAPHIE_WEB_ROOT . '/publications/ajax.php?task=uploadAttachment',
					'upload_url' => BIBLIOGRAPHIE_WEB_ROOT . '/attachments/',
					'image_versions' => array()
				)
		);

		header('Pragma: no-cache');
		header('Cache-Control: private, no-cache');
		header('Content-Disposition: inline; filename="files.json"');
		header('X-Content-Type-Options: nosniff');
		header('Access-Control-Allow-Origin: *');
		header('Access-Control-Allow-Methods: OPTIONS, HEAD, GET, POST, PUT, DELETE');
		header('Access-Control-Allow-Headers: X-File-Name, X-File-Type, X-File-Size');

		switch ($_SERVER['REQUEST_METHOD']) {
			case 'OPTIONS':
				break;
			case 'HEAD':
			case 'GET':
				$upload_handler->get();
				break;
			case 'POST':
				$upload_handler->post();
				break;
			case 'DELETE':
				$upload_handler->delete();
				break;
			default:
				header('HTTP/1.1 405 Method Not Allowed');
		}
		break;

	case 'deletePublicationConfirm':
		$publication = bibliographie_publications_get_data($_GET['pub_id']);

		if (is_object($publication)) {
			$text = 'You are about to delete the following publication.'
				. '<p>' . bibliographie_publications_parse_data($publication->pub_id) . '</p>'
				. 'If you are sure, click "delete" below!'
				. '<p class="success"><a href="' . BIBLIOGRAPHIE_WEB_ROOT . '/publications/?task=deletePublication&amp;pub_id=' . ((int) $publication->pub_id) . '">' . bibliographie_icon_get('page-white-delete') . ' Delete!</a></p>'
				. 'If you dont want to delete the publication, press "cancel" below!';
		}

		bibliographie_dialog_create('deletePublicationConfirm_' . ((int) $_GET['pub_id']), 'Confirm delete', $text);
		break;

	case 'exportChooseType':
		$publications = bibliographie_publications_get_cached_list($_GET['exportList']);

		if (is_array($publications) and count($publications) > 0) {
			$title = 'Choose export format';
			$text = '<h3>Export publications</h3>
<p class="notice">You\'re about to export ' . count($publications) . ' publication(s). Please choose the format that you want to export into.</p>
<label for="exportTarget" class="block">Format</label>
<select id="exportTarget" name="exportTarget" style="width: 100%">
	<option value="bibTex">BibTeX</option>
	<option value="ris">RIS</option>
	<option value="rtf">RTF</option>
	<option value="html">HTML</option>
	<option value="text">Text</option>
</select>
<label for="exportStyle" class="block">Style</label>
<select id="exportStyle" name="exportStyle" style="width: 100%">
	<option value="standard">Standard</option>
</select>';
		}

		bibliographie_dialog_create('exportChooseType_' . htmlspecialchars($_GET['exportList']), $title, $text);
		break;

	case 'exportPublications':
		$publications = bibliographie_publications_get_cached_list($_GET['exportList']);

		if (is_array($publications) and count($publications) > 0) {
			if (in_array($_GET['target'], array('html', 'text'))) {
				$text = bibliographie_publications_parse_list($publications, $_GET['target']);
			} else {
				$publications = array2csv($publications);

				$result = DB::getInstance()->prepare("SELECT
	`pub_id`,
	`pub_type`,
	`bibtex_id`,
	`address`,
	`booktitle`,
	`chapter`,
	`edition`,
	`howpublished`,
	`institution`,
	`journal`,
	`month`,
	`note`,
	`number`,
	`organization`,
	`pages`,
	`publisher`,
	`school`,
	`series`,
	`title`,
	`url`,
	`volume`,
	`year`
FROM
	`" . BIBLIOGRAPHIE_PREFIX . "publication`
WHERE
	FIND_IN_SET(`pub_id`, :set) ORDER BY `title`");
				$result->setFetchMode(PDO::FETCH_ASSOC);
				$result->bindParam('set', $publications);
				$result->execute();

				if ($result->rowCount() > 0) {
					if (in_array($_GET['target'], array('bibTex', 'rtf', 'ris'))) {
						$bibtex = new Structures_BibTex(array(
								'stripDelimiter' => true,
								'validate' => true,
								'unwrap' => true,
								'extractAuthors' => true,
								'escapeChars' => false
							));

						$publications = $result->fetchAll();

						foreach ($publications as $publication) {
							$publication['entryType'] = $publication['pub_type'];
							if (empty($publication['bibtex_id']))
								$publication['bibtex_id'] = md5($publication['title']);
							$publication['cite'] = $publication['bibtex_id'];

							$authors = bibliographie_publications_get_authors($publication['pub_id']);
							$editors = bibliographie_publications_get_editors($publication['pub_id']);

							unset($publication['pub_id'], $publication['pub_type'], $publication['bibtex_id']);

							if (is_array($authors) and count($authors) > 0)
								foreach ($authors as $author)
									$publication['author'][] = bibliographie_authors_parse_data($author, array('forBibTex' => true));

							if (is_array($editors) and count($editors) > 0)
								foreach ($editors as $editor)
									$publication['editor'][] = bibliographie_authors_parse_data($editor, array('forBibTex' => true));

							$_publication = array();
							foreach ($publication as $key => $field)
								if (!empty($field))
									$_publication[$key] = $field;
							$bibtex->data[] = $_publication;
						}

						if ($_GET['target'] == 'bibTex') {
							$text = $bibtex->bibtex();
						} elseif ($_GET['target'] == 'ris') {
							$risTranslator = new \bibliographie\RISTranslator();
							$risWriter = new \LibRIS\RISWriter();
							$text = $risWriter->writeRecords($risTranslator->bibtex2ris($bibtex->data));
						} elseif ($_GET['target'] == 'rtf') {
							$rtf = $bibtex->rtf();
							$file = fopen(BIBLIOGRAPHIE_ROOT_PATH . '/cache/export_' . md5($rtf) . '.rtf', 'w+');
							fwrite($file, $rtf);
							fclose($file);
							header('Location: ' . BIBLIOGRAPHIE_ROOT_PATH . '/cache/export_' . md5($rtf) . '.rtf');
						}
					}
				}
			}

			bibliographie_dialog_create(
				'bibliographie_export_' . $_GET['exportList'], 'Export result', '<a id="bibliographie_export_' . $_GET['exportList'] . '_copy" href="javascript:;">' . bibliographie_icon_get('briefcase') . ' Copy to clipboard and close dialog.</a>
<pre id="bibliographie_export_' . $_GET['exportList'] . '_result" class="bibliographie_export_result">' . $text . '</pre>'
			);
		}
		break;

	case 'checkData':
		if ($_GET['subTask'] == 'approvePerson') {
			if (is_numeric($_GET['selectedPerson'])) {
				if (!is_array($_SESSION['publication_prefetchedData_checked'][$_GET['entryID']]['checked_' . $_GET['role']]))
					$_SESSION['publication_prefetchedData_checked'][$_GET['entryID']]['checked_' . $_GET['role']] = array();

				$_SESSION['publication_prefetchedData_checked'][$_GET['entryID']]['checked_' . $_GET['role']][$_GET['personID']] = (int) $_GET['selectedPerson'];
				echo bibliographie_icon_get('tick') . ' ' . bibliographie_authors_parse_data($_GET['selectedPerson']) . ' has been approved as ' . htmlspecialchars($_GET['role']) . '!';
			}else
				echo '<p class="error">You did not select an author from the dropdown list!</p>';
		}elseif ($_GET['subTask'] == 'undoApproval') {
			$_SESSION['publication_prefetchedData_checked'][$_GET['entryID']]['checked_' . $_GET['role']][$_GET['personID']] = null;
		} elseif ($_GET['subTask'] == 'createPerson') {
			$data = bibliographie_authors_create_author($_GET['first'], $_GET['von'], $_GET['last'], $_GET['jr'], '', '', '');
			if (is_array($data)) {
				$_SESSION['publication_prefetchedData_checked'][$_GET['entryID']]['checked_' . $_GET['role']][$_GET['personID']] = $data['author_id'];
				echo bibliographie_icon_get('tick') . ' ' . bibliographie_authors_parse_data($data['author_id']) . ' has been created and approved as ' . htmlspecialchars($_GET['role']) . '!';
			}else
				echo '<p class="error">Person could not be created!</p>';
		}elseif ($_GET['subTask'] == 'approveEntry') {
			$text = '<p class="error">An error occured!</p>';
			$status = 'error';
			if (is_numeric($_GET['entryID'])) {
				$_GET['entryID'] = (int) $_GET['entryID'];
				if (count($_SESSION['publication_prefetchedData_checked'][$_GET['entryID']]['checked_author']) == count($_SESSION['publication_prefetchedData_unchecked'][$_GET['entryID']]['author'])
					and count($_SESSION['publication_prefetchedData_checked'][$_GET['entryID']]['checked_editor']) == count($_SESSION['publication_prefetchedData_unchecked'][$_GET['entryID']]['editor'])) {

					$_SESSION['publication_prefetchedData_checked'][$_GET['entryID']] = array_merge($_SESSION['publication_prefetchedData_checked'][$_GET['entryID']], $_SESSION['publication_prefetchedData_unchecked'][$_GET['entryID']]);

					$text = bibliographie_icon_get('tick') . ' Parsed entry has been approved and added to queue!';
					$status = 'success';
				}else
					$text = bibliographie_icon_get('cross') . ' Sorry but you cannot approve an entry if there are authors left that are not approved!';
			}

			echo json_encode(array(
				'text' => $text,
				'status' => $status
			));
		}
		break;

	case 'fetchData_proceed':
		if ($_POST['source'] == 'direct' or $_POST['source'] == 'remote') {
			if ($_POST['step'] == '1') {
				if ($_POST['source'] == 'direct') {
					?>

					<label for="direct" class="block"><?php echo bibliographie_icon_get('page-white-code') ?> Input text containing bibliographic information</label>
					<textarea id="direct" name="direct" rows="20" cols="20" style="width: 100%;"></textarea>
					<button onclick="bibliographie_publications_fetch_data_proceed({'source': 'direct', 'step': '2', 'direct': $('#direct').val()})">Parse!</button>
					<?php
				} else {
					?>

					<label for="remote" class="block"><?php echo bibliographie_icon_get('page-white-code') ?> Input URL to text containing bibliographic information</label>
					<input id="remote" name="remote" style="width: 100%" />
					<button onclick="bibliographie_publications_fetch_data_proceed({'source': 'remote', 'step': '2', 'remote': $('#remote').val()})">Parse!</button>
					<?php
				}
			} elseif ($_POST['step'] == '2') {
				if ($_POST['source'] == 'direct' and empty($_POST['direct'])) {
					?>

					<p class="error">Your input was empty! Please <a href="javascript:;" onclick="bibliographie_publications_fetch_data_proceed({'source': 'direct', 'step': '1'})">start again</a>!</p>
					<?php
					break;
				} elseif ($_POST['source'] == 'remote' and !is_url($_POST['remote'])) {
					?>

					<p class="error">You did not provide a proper url. Please <a href="javascript:;" onclick="bibliographie_publications_fetch_data_proceed({'source': 'direct', 'step': '1'})">start again</a>!</p>
					<?php
				}

				$bibtex = new Structures_BibTex(array(
						'stripDelimiter' => true,
						'validate' => true,
						'unwrap' => true,
						'extractAuthors' => true
					));
				$ris = new \LibRIS\RisReader();
				$risTranslator = new \bibliographie\RISTranslator();

				$input = (string) '';
				if ($_POST['source'] == 'direct')
					$input = strip_tags($_POST['direct']);
				else
					$input = strip_tags(file_get_contents($_POST['remote']));

				$bibtex->loadContent($input);
				$bibtex->parse();
				$ris->parseString(str_replace("\n", \LibRIS\RISReader::RIS_EOL, $input));

				if (count($bibtex->data) > 0 or count($ris->getRecords()) > 0) {
					foreach ($bibtex->data as $key => $row) {
						$bibtex->data[$key]['pub_type'] = $row['entryType'];
						$bibtex->data[$key]['bibtex_id'] = $row['cite'];
						$bibtex->data[$key]['note'] = 'Imported from ' . $_POST['source'] . '...';
					}

					$result = array_merge($bibtex->data, $risTranslator->ris2bibtex($ris->getRecords()));

					$_SESSION['publication_prefetchedData_unchecked'] = $result;
					?>

					<p class="success">Parsing of your input was successful!</p>
					<p>Your input contained <strong><?php echo count($result) ?></strong> entries. You can now proceed and check your fetched entries!</p>
					<div class="submit"><button onclick="window.location = '<?php echo BIBLIOGRAPHIE_WEB_ROOT ?>/publications/?task=checkData';">Check fetched data</button></div>
					<?php
				} else {
					?>

					<p class="error">There was an error while parsing! Please <a href="javascript:;" onclick="bibliographie_publications_fetch_data_proceed({'source': <?php echo $_GET['source'] ?>, 'step': '1'})">start again</a>!</p>
					<?php
				}
			}
		} elseif ($_POST['source'] == 'isbndb') {
			if ($_POST['step'] == '1') {
				?>

				<div style="float: right; width: 50%">
					<label for="value" class="block">Query</label>
					<input type="text" id="value" name="value" style="width: 100%" />
				</div>

				<label for="key" class="block">Range</label>
				<select id="key" name="key" style="width: 45%">
					<option value="isbn">ISBN #</option>
					<option value="title">In field title</option>
					<option value="combined">In fields title, authors and publisher</option>
					<option value="full">Fulltext</option>
				</select>

				<button onclick="bibliographie_publications_fetch_data_proceed({'source': 'isbndb', 'step': '2', 'key': $('#key').val(), 'value': $('#value').val()})">Search</button>
				<?php
			} elseif ($_POST['step'] == '2') {
				$response = '';
				if (in_array($_POST['key'], array('isbn', 'full', 'title', 'combined'))) {
					if ($_POST['key'] == 'isbn')
						$_POST['value'] = str_replace('-', '', $_POST['value']);

					$response = file_get_contents('http://www.isbndb.com/api/books.xml?access_key=' . BIBLIOGRAPHIE_ISBNDB_KEY . '&results=authors&index1=' . $_POST['key'] . '&value1=' . strip_tags($_POST['value']));
				}

				$response = json_decode(json_encode(simplexml_load_string($response)), true);
				if (is_array($response['BookList']['BookData'])) {
					/**
					 * Map unique results to the structure of multiple results for convenience...
					 */
					if ($response['BookList']['@attributes']['shown_results'] == '1') {
						$dummy = $response['BookList']['BookData'];
						$response['BookList']['BookData'] = null;
						$response['BookList']['BookData'][] = $dummy;
					}

					$i = 0;
					foreach ($response['BookList']['BookData'] as $book) {
						$_SESSION['publication_prefetchedData_unchecked'][$i]['title'] = $book['Title'];
						if (is_string($book['TitleLong']))
							$_SESSION['publication_prefetchedData_unchecked'][$i]['title'] = $book['TitleLong'];

						$_SESSION['publication_prefetchedData_unchecked'][$i]['isbn'] = $book['@attributes']['isbn'];
						if (!empty($book['@attributes']['isbn13']))
							$_SESSION['publication_prefetchedData_unchecked'][$i]['isbn'] = $book['@attributes']['isbn13'];

						$_SESSION['publication_prefetchedData_unchecked'][$i]['publisher'] = $book['PublisherText'];

						if (is_array($book['Authors']['Person'])) {
							foreach ($book['Authors']['Person'] as $author) {
								$author = explode(',', $author);
								$_SESSION['publication_prefetchedData_unchecked'][$i]['author'][] = array(
									'first' => $author[1],
									'von' => '',
									'last' => $author[0],
									'jr' => ''
								);
							}
						} else {
							$author = explode(',', $book['Authors']['Person']);
							$_SESSION['publication_prefetchedData_unchecked'][$i]['author'][0] = array(
								'first' => $author[1],
								'von' => '',
								'last' => $author[0],
								'jr' => ''
							);
						}
						$_SESSION['publication_prefetchedData_unchecked'][$i]['note'] = 'Imported from isbndb.com';
						$i++;
					}
					?>

					<p class="success">Parsing of your search was successful!</p>
					<p>Your search contained <strong><?php echo ((int) $response['BookList']['@attributes']['total_results']) ?></strong> entries. Due to service limitations only the first 10 entries can be shown. If the result didn't contain the book you searched for, try to narrow down your search via the query!<br />You can now proceed and check your fetched entries! </p>
					<div class="submit"><button onclick="window.location = '<?php echo BIBLIOGRAPHIE_WEB_ROOT ?>/publications/?task=checkData';">Check fetched data</button></div>
					<?php
				} else {
					?>

					<p class="error">Your search result was empty! Please <a href="javascript:;" onclick="bibliographie_publications_fetch_data_proceed({'source': 'isbndb', 'step': '1'})">start again</a>!</p>
					<?php
				}
			}
		} elseif ($_POST['source'] == 'pubmed') {
			if ($_POST['step'] == '1') {
				?>

				<label for="pubmedQuery" class="block"><?php echo bibliographie_icon_get('database') ?> PubMed query</label>
				<input id="pubmedQuery" name="pubmedQuery" style="width: 100%;" />
				<button onclick="bibliographie_publications_fetch_data_proceed({'source': 'pubmed', 'step': '2', 'pubmedQuery': $('#pubmedQuery').val()})">Search & parse!</button>
				<?php
			} elseif ($_POST['step'] == '2') {
				if (!empty($_POST['pubmedQuery'])) {
					$searchResult = new SimpleXMLElement(file_get_contents('http://eutils.ncbi.nlm.nih.gov/entrez/eutils/esearch.fcgi?db=pubmed&retmax=0&usehistory=y&term=' . urlencode($_POST['pubmedQuery'])));
					$dataResult = new SimpleXMLElement(file_get_contents('http://eutils.ncbi.nlm.nih.gov/entrez/eutils/esummary.fcgi?db=pubmed&retmode=xml&query_key=' . $searchResult->QueryKey . '&WebEnv=' . $searchResult->WebEnv . '&retstart=0&retmax=50'));
					$dataResult = (array) $dataResult;

					$title = 5;
					$authors = 3;
					$year = 0;
					$doi = 17;

					if (is_array($dataResult['DocSum']) and count($dataResult['DocSum']) > 0) {
						$i = 0;
						foreach ($dataResult['DocSum'] as $document) {
							$document = (array) $document;
							$document = $document['Item'];
							$authorsList = (array) $document[$authors]->Item;

							$_SESSION['publication_prefetchedData_unchecked'][$i]['title'] = $document[$title];
							foreach ($authorsList as $author)
								if (is_string($author))
									$_SESSION['publication_prefetchedData_unchecked'][$i]['author'][] = array(
										'last' => $author,
										'first' => '',
										'von' => '',
										'jr' => ''
									);

							if (is_string($document[$doi]))
								$_SESSION['publication_prefetchedData_unchecked'][$i]['doi'] = $document[$doi];
							if (is_string($document[$year]))
								$_SESSION['publication_prefetchedData_unchecked'][$i]['year'] = $document[$year];
							$_SESSION['publication_prefetchedData_unchecked'][$i]['note'] = 'Imported from PubMed';

							$i++;
						}
						?>

						<p class="success">Parsing of your search was successful, found <em><?php echo $searchResult->Count ?></em> articles and fetching data of <?php echo count($dataResult['DocSum']) ?>.</p>
						<p>Narrow down your search, if you didnt get the results you desired!</p>
						<p>
							<strong>Translated query</strong>: <?php echo $searchResult->QueryTranslation ?><br /><br />
						<?php
						foreach ($searchResult->TranslationStack->TermSet as $term)
							echo '<strong>',
							$term->Term,
							'</strong> in <em>' . $term->Field . '</em> with ' . $term->Count . ' results...<br />';
						?>

						</p>
						<p>You can now proceed and check your fetched entries!</p>
						<div class="submit"><button onclick="window.location = '<?php echo BIBLIOGRAPHIE_WEB_ROOT ?>/publications/?task=checkData';">Check fetched data</button></div>
						<?php
					}else {
						?>

						<p class="error">Your PubMed result was empty! Please <a href="javascript:;" onclick="bibliographie_publications_fetch_data_proceed({'source': 'pubmed', 'step': '1'})">start again</a>!</p>
							<?php
						}
					} else {
						?>

					<p class="error">Your PubMed query was empty! Please <a href="javascript:;" onclick="bibliographie_publications_fetch_data_proceed({'source': 'pubmed', 'step': '1'})">start again</a>!</p>
					<?php
				}
			}
		}
		break;

	case 'checkTitle':
		$result = array(
			'count' => 0,
			'results' => array(),
			'status' => 'error',
			'exact_match' => false
		);

		if (mb_strlen($_GET['title']) >= BIBLIOGRAPHIE_SEARCH_MIN_CHARS) {
			$result['status'] = 'success';

			$expandedTitle = $_GET['title'];

			$pub_id = 0;
			if (is_numeric($_GET['pub_id']))
				$pub_id = (int) $_GET['pub_id'];

			$similarTitles = DB::getInstance()->prepare("SELECT * FROM (
	SELECT `pub_id`, `title`, (`searchRelevancy` * 10 - (ABS(LENGTH(`title`) - LENGTH(:title) / 2))) AS `relevancy`  FROM (
		SELECT `pub_id`, `title`, (MATCH(`title`) AGAINST (:title IN NATURAL LANGUAGE MODE)) AS `searchRelevancy`
		FROM `" . BIBLIOGRAPHIE_PREFIX . "publication`
		WHERE `pub_id` != :pub_id
	) fullTextSearch
) calculatedRelevancy
WHERE
	`relevancy` > 0
ORDER BY
	`relevancy` DESC
LIMIT
	100");

			$similarTitles->execute(array(
				'title' => $expandedTitle,
				'pub_id' => $pub_id
			));

			$result['count'] = $similarTitles->rowCount();

			if ($result['count'] > 0)
				$result['results'] = $similarTitles->fetchAll(PDO::FETCH_OBJ);

			foreach ($result['results'] as $key => $publication) {
				$sameAuthors = array_intersect(bibliographie_publications_get_authors($publication->pub_id), csv2array($_GET['author']));
				if (count($sameAuthors) > 0) {
					if ($result['results'][$key]->title == $_GET['title'] or count($sameAuthors) == count(csv2array($_GET['author'])))
						$result['exact_match'] = true;

					$result['results'][$key]->relevancy += count($sameAuthors) * 30;
					$result['results'][$key]->title = '<strong>' . $result['results'][$key]->title . '</strong> <em>(' . count($sameAuthors) . ' similar authors)</em>';
				}
			}

			usort($result['results'], function ($a, $b) {
					if ($a->relevancy == $b->relevancy)
						return 0;
					return ($a->relevancy < $b->relevancy) ? 1 : -1;
				});
		}

		echo json_encode($result);
		break;

	case 'getFields':
		$result = array();
		if (array_key_exists(mb_strtolower($_GET['type']), $bibliographie_publication_fields)) {
			foreach ($bibliographie_publication_fields[mb_strtolower($_GET['type'])] as $flag => $fields) {
				foreach ($fields as $field)
					$result[] = array('field' => $field, 'flag' => $flag);
			}
		}

		echo json_encode($result);
		break;
}

require BIBLIOGRAPHIE_ROOT_PATH . '/close.php';