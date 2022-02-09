function fetchStats() {
	fetch("admin-ajax.php?action=stats")
		.then((response) => response.json())
		.then((data) => {

			const elm = document.querySelector(".wordpress-meilisearch-realtime-numberOfDocuments");

			if(elm){
				elm.innerHTML = data.numberOfDocuments;
			}

			if (data.isIndexing == true) {
				setTimeout(fetchStats, 500);
			}
		});
}

fetchStats();
