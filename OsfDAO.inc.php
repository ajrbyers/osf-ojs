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
}

