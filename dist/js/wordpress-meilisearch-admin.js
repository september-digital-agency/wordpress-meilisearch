function fetchStats() {
	fetch("admin-ajax.php?action=stats")
		.then((response) => response.json())
		.then((data) => {
			document.querySelector(
				".wordpress-meilisearch-realtime-numberOfDocuments"
			).innerHTML = data.numberOfDocuments;

			if (data.isIndexing == true) {
				setTimeout(fetchStats, 500);
			}
		});
}

fetchStats();
