(()=>{var e,r={101:()=>{var e=document.querySelector("[data-meilisearch-stats-url]");if(e)var r=e.getAttribute("data-meilisearch-stats-url"),n=setInterval((function(){fetch(r,{credentials:"include"}).then((function(e){return e.json()})).then((function(e){var r=document.querySelector(".wordpress-meilisearch-realtime-numberOfDocuments");r&&(r.innerHTML=e.index.numberOfDocuments),1!=e.index.isIndexing&&clearInterval(n)}))}),1e3)},660:()=>{}},n={};function t(e){var i=n[e];if(void 0!==i)return i.exports;var a=n[e]={exports:{}};return r[e](a,a.exports,t),a.exports}t.m=r,e=[],t.O=(r,n,i,a)=>{if(!n){var o=1/0;for(c=0;c<e.length;c++){for(var[n,i,a]=e[c],s=!0,l=0;l<n.length;l++)(!1&a||o>=a)&&Object.keys(t.O).every((e=>t.O[e](n[l])))?n.splice(l--,1):(s=!1,a<o&&(o=a));if(s){e.splice(c--,1);var u=i();void 0!==u&&(r=u)}}return r}a=a||0;for(var c=e.length;c>0&&e[c-1][2]>a;c--)e[c]=e[c-1];e[c]=[n,i,a]},t.o=(e,r)=>Object.prototype.hasOwnProperty.call(e,r),(()=>{var e={648:0,461:0};t.O.j=r=>0===e[r];var r=(r,n)=>{var i,a,[o,s,l]=n,u=0;if(o.some((r=>0!==e[r]))){for(i in s)t.o(s,i)&&(t.m[i]=s[i]);if(l)var c=l(t)}for(r&&r(n);u<o.length;u++)a=o[u],t.o(e,a)&&e[a]&&e[a][0](),e[o[u]]=0;return t.O(c)},n=self.webpackChunkwordpress_meilisearch_plugin=self.webpackChunkwordpress_meilisearch_plugin||[];n.forEach(r.bind(null,0)),n.push=r.bind(null,n.push.bind(n))})(),t.O(void 0,[461],(()=>t(101)));var i=t.O(void 0,[461],(()=>t(660)));i=t.O(i)})();