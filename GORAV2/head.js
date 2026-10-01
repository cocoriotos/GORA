fetch("head.html")
				.then(response => response.text())
				.then(data => {
					document.head.innerHTML += data;
				});

const sessionTimeoutScript = document.createElement('script');
sessionTimeoutScript.src = 'session-timeout.js';
sessionTimeoutScript.defer = true;
document.head.appendChild(sessionTimeoutScript);