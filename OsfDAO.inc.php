<?php

/**
 *
 * Plugin for submitting an article from OSF.io
 * Written by Andy Byers, Ubiquity Press
 *
 */

class OsfDAO extends DAO {

	function getInsertArticleId() {
		return $this->getInsertId('articles', 'article_id');
	}

	function getInsertArticleFileId() {
		return $this->getInsertId('article_files', 'file_id');
	}

	function getInsertAuthorId() {
		return $this->getInsertId('authors', 'author_id');
	}

	function create_article($params) {
		$sql = <<< EOF
			INSERT INTO articles
			(locale, user_id, journal_id, language, current_round)
			VALUES
			(?, ?, ?, ?, ?)
EOF;
		$commit = $this->update($sql, $params);
		$article_id = $this->getInsertArticleId();

		return $article_id;
	}
	function create_file($params) {
		$sql = <<< EOF
			INSERT INTO article_files
			(revision, article_id, original_file_name, file_stage, date_uploaded, date_modified, round, file_name, file_type, file_size)
			VALUES
			(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
EOF;
		$commit = $this->update($sql, $params);
		$file_id = $this->getInsertArticleFileId();

		return $file_id;
	}

	function update_file($params) {
		$sql = <<< EOF
			UPDATE article_files
			SET file_name = ?, file_type = ?, file_size = ?
			WHERE file_id = ?
EOF;
		$commit = $this->update($sql, $params);
	}

	function update_article_submission_file($params) {
		$sql = <<< EOF
			UPDATE articles
			SET submission_file_id = ?
			WHERE article_id = ?
EOF;
		$commit = $this->update($sql, $params);
	}

	function complete_article($params) {
		$sql = <<< EOF
			UPDATE articles
			SET section_id = ?, submission_progress = ?, date_submitted = ?
			WHERE article_id = ?
EOF;
		$commit = $this->update($sql, $params);
	}

	function add_author($params) {
		$sql = <<< EOF
			INSERT INTO authors
			(submission_id, primary_contact, seq, first_name, middle_name, last_name, country, email, url)
			VALUES
			(?, ?, ?, ?, ?, ?, ?, ?, ?)
EOF;
		$commit = $this->update($sql, $params);
		$author_id = $this->getInsertAuthorId();

		return $author_id;
	}

	function add_author_settings($author_id, $locale, $params){
		foreach ($params as $key => $value) {
			$sql = <<< EOF
				INSERT INTO author_settings
				(author_id, locale, setting_name, setting_value, setting_type)
				VALUES
				(?, ?, ?, ?, ?)
EOF;
			$commit = $this->update($sql, array($author_id, $locale, $key, $value, 'string'));
		}
	}

	function add_article_settings($article_id, $locale, $params){
		foreach ($params as $key => $value) {
			$sql = <<< EOF
				INSERT INTO article_settings
				(article_id, locale, setting_name, setting_value, setting_type)
				VALUES
				(?, ?, ?, ?, ?)
EOF;
			$commit = $this->update($sql, array($article_id, $locale, $key, $value, 'string'));
		}
		

	}

}

