
// Mock Portfolio Data
let portfolio = [
    { name: "Bitcoin", symbol: "BTC", amount: 0.5 },
    { name: "Ethereum", symbol: "ETH", amount: 2.0 }
];

// Function to fetch real-time cryptocurrency prices
async function fetchCryptoPrice(symbol) {
    const response = await fetch(`https://api.coingecko.com/api/v3/simple/price?ids=${symbol}&vs_currencies=usd`);
    const data = await response.json();
    return data[symbol]?.usd || 0;
}

// Function to render the portfolio
async function renderPortfolio() {
    const tableBody = document.getElementById("portfolio-entries");
    tableBody.innerHTML = "";

    for (const entry of portfolio) {
        const price = await fetchCryptoPrice(entry.symbol.toLowerCase());
        const totalValue = (price * entry.amount).toFixed(2);

        const row = document.createElement("tr");
        row.innerHTML = `
            <td>${entry.name}</td>
            <td>${entry.symbol}</td>
            <td>${entry.amount}</td>
            <td>$${price.toFixed(2)}</td>
            <td>$${totalValue}</td>
            <td>
                <button onclick="deleteHolding('${entry.symbol}')">Delete</button>
            </td>
        `;

        tableBody.appendChild(row);
    }
}

// Add new holding
document.getElementById("add-holding-form").addEventListener("submit", (event) => {
    event.preventDefault();

    const name = document.getElementById("crypto-name").value;
    const symbol = document.getElementById("crypto-symbol").value;
    const amount = parseFloat(document.getElementById("crypto-amount").value);

    portfolio.push({ name, symbol, amount });
    renderPortfolio();
    alert("Holding added successfully!");
    document.getElementById("add-holding-modal").classList.add("hidden");
});

// Delete holding
function deleteHolding(symbol) {
    portfolio = portfolio.filter((entry) => entry.symbol !== symbol);
    renderPortfolio();
    alert("Holding deleted successfully!");
}

// Show Add Holding Modal
document.getElementById("add-holding-btn").addEventListener("click", () => {
    document.getElementById("add-holding-modal").classList.remove("hidden");
});

// Hide Add Holding Modal
document.getElementById("cancel-add").addEventListener("click", () => {
    document.getElementById("add-holding-modal").classList.add("hidden");
});

// Initial Render
renderPortfolio();
