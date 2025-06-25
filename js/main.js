document.addEventListener('DOMContentLoaded', () => {
    const confessionForm = document.getElementById('confessionForm');
    const confessionsFeed = document.getElementById('confessionsFeed');

    // Load confessions when page loads
    loadConfessions();

    // Handle form submission
    confessionForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(confessionForm);

        try {
            const response = await fetch('backend/submit_confession.php', {
                method: 'POST',
                body: formData
            });

            if (!response.ok) throw new Error('Failed to submit confession');

            const result = await response.json();
            if (result.success) {
                confessionForm.reset();
                loadConfessions();
            } else {
                alert('Error submitting confession: ' + result.message);
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Failed to submit confession. Please try again.');
        }
    });

    // Function to load confessions
    async function loadConfessions() {
        try {
            const response = await fetch('backend/get_confessions.php');
            if (!response.ok) throw new Error('Failed to fetch confessions');

            const confessions = await response.json();
            displayConfessions(confessions);
        } catch (error) {
            console.error('Error:', error);
            confessionsFeed.innerHTML = '<p class="error">Failed to load confessions. Please refresh the page.</p>';
        }
    }

    // Function to display confessions
    function displayConfessions(confessions) {
        confessionsFeed.innerHTML = '';
        confessions.forEach(confession => {
            const confessionCard = document.createElement('div');
            confessionCard.className = 'confession-card';
            
            const timestamp = new Date(confession.timestamp).toLocaleString();
            
            confessionCard.innerHTML = `
                <p>${escapeHtml(confession.content)}</p>
                <div class="confession-time">${timestamp}</div>
            `;

            confessionsFeed.appendChild(confessionCard);
        });
    }

    // Helper function to escape HTML and prevent XSS
    function escapeHtml(unsafe) {
        return unsafe
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }
});