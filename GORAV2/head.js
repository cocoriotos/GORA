fetch("head.html")
				.then(response => response.text())
				.then(data => {
					document.head.innerHTML += data;
				});

const sessionTimeoutScript = document.createElement('script');
sessionTimeoutScript.src = 'session-timeout.js?v=20261001';
sessionTimeoutScript.defer = true;
document.head.appendChild(sessionTimeoutScript);