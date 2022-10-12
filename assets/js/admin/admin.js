const statsElm = document.querySelector('[data-meilisearch-stats-url]');
if(statsElm){
	const url = statsElm.getAttribute('data-meilisearch-stats-url');

	const interval = setInterval(function(){

		fetch(url, {credentials: "include"})
			.then((response) => response.json())
			.then((data) => {

				const elm = document.querySelector(".wordpress-meilisearch-realtime-numberOfDocuments");

				if(elm){
					elm.innerHTML = data.index.numberOfDocuments;
				}

				const elm2 = document.querySelector(".wordpress-meilisearch-realtime-numberTotal");

				if(elm2){
					elm2.innerHTML = data.total;
				}

				if (data.index.isIndexing != true) {
					clearInterval(interval);
				}
			});

	}, 1000);

}
