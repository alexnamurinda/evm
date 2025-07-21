// Configuration - Replace with your actual Google Sheets details
const CONFIG = {
    SHEET_ID: '1jddhIBS_JxfDq-bwMgAmqBI8VBX2VCAhT6wk2aBcBmU', // Replace with your Google Sheet ID
    SHEET_URL: 'https://script.google.com/macros/s/AKfycbx-eHp2Au6bCdpW6CbjQ1CzaCnqy1ET6MJVXXvG4-hUNKwLj9J-ClLAM9N39la7TKJVig/exec' // Replace with your Web App URL
};

// Global variables
let deliveryData = [];

// DOM Elements
const newDeliveryBtn = document.getElementById('newDeliveryBtn');
const viewDashboardBtn = document.getElementById('viewDashboardBtn');
const newDeliverySection = document.getElementById('newDeliverySection');
const dashboardSection = document.getElementById('dashboardSection');
const deliveryForm = document.getElementById('deliveryForm');
const loadingOverlay = document.getElementById('loadingOverlay');

// Navigation
newDeliveryBtn.addEventListener('click', () => {
    showSection('new');
});

viewDashboardBtn.addEventListener('click', () => {
    showSection('dashboard');
    loadDashboardData();
});

function showSection(section) {
    // Remove active class from all sections and buttons
    document.querySelectorAll('.section').forEach(s => s.classList.remove('active'));
    document.querySelectorAll('.nav-btn').forEach(b => b.classList.remove('active'));
    
    if (section === 'new') {
        newDeliverySection.classList.add('active');
        newDeliveryBtn.classList.add('active');
    } else {
        dashboardSection.classList.add('active');
        viewDashboardBtn.classList.add('active');
    }
}

// Form submission
deliveryForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const formData = {
        invoiceNumber: document.getElementById('invoiceNumber').value.trim(),
        deliveryDate: document.getElementById('deliveryDate').value,
        dispatchTime: document.getElementById('dispatchTime').value,
        arrivalTime: document.getElementById('arrivalTime').value,
        storeSupervisor: document.getElementById('storeSupervisor').value.trim(),
        deliveryPerson: document.getElementById('deliveryPerson').value.trim(),
        vehicleNumber: document.getElementById('vehicleNumber').value.trim()
    };
    
    // Validate arrival time is after dispatch time
    if (formData.dispatchTime && formData.arrivalTime) {
        const dispatch = new Date(`2000-01-01T${formData.dispatchTime}`);
        const arrival = new Date(`2000-01-01T${formData.arrivalTime}`);
        
        if (arrival <= dispatch) {
            alert('Arrival time must be after dispatch time');
            return;
        }
    }
    
    await submitDeliveryRecord(formData);
});

async function submitDeliveryRecord(data) {
    showLoading(true);
    
    try {
        console.log('Submitting data:', data);
        
        // Use fetch with proper headers for Google Apps Script
        const response = await fetch(CONFIG.SHEET_URL, {
            method: 'POST',
            headers: {
                'Content-Type': 'text/plain',
            },
            body: JSON.stringify({
                action: 'addRecord',
                data: data
            })
        });
        
        if (response.ok) {
            const result = await response.json();
            console.log('Server response:', result);
            
            if (result.success) {
                deliveryForm.reset();
                alert('Delivery record added successfully!');
                // Set today's date again for next entry
                document.getElementById('deliveryDate').value = new Date().toISOString().split('T')[0];
            } else {
                throw new Error(result.error || 'Failed to add record');
            }
        } else {
            throw new Error(`Network response was not ok: ${response.status}`);
        }
        
    } catch (error) {
        console.error('Error submitting record:', error);
        alert('Error submitting record: ' + error.message);
    }
    
    showLoading(false);
}

async function loadDashboardData() {
    showLoading(true);
    
    try {
        console.log('Loading dashboard data...');
        
        // Fetch data from Google Sheets
        const response = await fetch(`${CONFIG.SHEET_URL}?action=getData`);
        
        if (response.ok) {
            const data = await response.json();
            console.log('Received data:', data);
            
            if (data.success) {
                deliveryData = data.records || [];
                console.log('Loaded records:', deliveryData.length);
                console.log('Sample record:', deliveryData[0]);
                
                // Clean and validate the data
                deliveryData = deliveryData.filter(record => {
                    return record.invoiceNumber && 
                           record.deliveryDate && 
                           record.deliveryDate !== 'Invalid Date' &&
                           !record.deliveryDate.includes('1899');
                });
                
                console.log('Filtered records:', deliveryData.length);
                updateDashboard();
                populateTable();
            } else {
                console.error('Error loading data:', data.error);
                showDemoData();
            }
        } else {
            throw new Error(`Failed to fetch data: ${response.status}`);
        }
        
    } catch (error) {
        console.error('Error fetching data:', error);
        showDemoData();
    }
    
    showLoading(false);
}

function showDemoData() {
    // Demo data for testing when Google Sheets is not available
    deliveryData = [
        {
            invoiceNumber: 'INV001',
            deliveryDate: '2025-07-20',
            dispatchTime: '09:00',
            arrivalTime: '09:30',
            duration: 30,
            status: 'Success',
            storeSupervisor: 'John Doe',
            deliveryPerson: 'Mike Wilson',
            vehicleNumber: 'UG-123-ABC'
        },
        {
            invoiceNumber: 'INV002',
            deliveryDate: '2025-07-19',
            dispatchTime: '10:00',
            arrivalTime: '11:00',
            duration: 60,
            status: 'Delayed',
            storeSupervisor: 'Jane Smith',
            deliveryPerson: 'Tom Brown',
            vehicleNumber: 'UG-456-DEF'
        }
    ];
    
    updateDashboard();
    populateTable();
}

function updateDashboard() {
    console.log('Updating dashboard with', deliveryData.length, 'records');
    
    if (!deliveryData || deliveryData.length === 0) {
        // Set all metrics to 0 if no data
        document.getElementById('dailySuccessRate').textContent = '0.0%';
        document.getElementById('dailyTotal').textContent = '0';
        document.getElementById('dailySuccessful').textContent = '0';
        document.getElementById('weeklySuccessRate').textContent = '0.0%';
        document.getElementById('weeklyDays').textContent = '0';
        document.getElementById('weeklyTotal').textContent = '0';
        document.getElementById('monthlySuccessRate').textContent = '0.0%';
        
        const progressBar = document.getElementById('targetProgress');
        progressBar.style.width = '0%';
        return;
    }

    const today = new Date().toISOString().split('T')[0];
    const currentWeek = getCurrentWeekDates();
    const currentMonth = getCurrentMonthDates();
    
    console.log('Today:', today);
    console.log('Current week:', currentWeek);
    console.log('Sample delivery dates:', deliveryData.slice(0, 3).map(d => d.deliveryDate));
    
    // Daily Performance
    const dailyDeliveries = deliveryData.filter(d => {
        const deliveryDate = normalizeDate(d.deliveryDate);
        return deliveryDate === today;
    });
    
    const dailySuccessful = dailyDeliveries.filter(d => d.status === 'Success').length;
    const dailySuccessRate = dailyDeliveries.length > 0 ? ((dailySuccessful / dailyDeliveries.length) * 100).toFixed(1) : 0;
    
    console.log('Daily deliveries:', dailyDeliveries.length, 'Successful:', dailySuccessful);
    
    document.getElementById('dailySuccessRate').textContent = `${dailySuccessRate}%`;
    document.getElementById('dailyTotal').textContent = dailyDeliveries.length;
    document.getElementById('dailySuccessful').textContent = dailySuccessful;
    
    // Weekly Performance
    const weeklyDeliveries = deliveryData.filter(d => {
        const deliveryDate = normalizeDate(d.deliveryDate);
        return currentWeek.includes(deliveryDate);
    });
    
    const weeklyDaysWithDeliveries = [...new Set(weeklyDeliveries.map(d => normalizeDate(d.deliveryDate)))].length;
    const weeklySuccessful = weeklyDeliveries.filter(d => d.status === 'Success').length;
    const weeklySuccessRate = weeklyDeliveries.length > 0 ? ((weeklySuccessful / weeklyDeliveries.length) * 100).toFixed(1) : 0;
    
    console.log('Weekly deliveries:', weeklyDeliveries.length, 'Successful:', weeklySuccessful);
    
    document.getElementById('weeklySuccessRate').textContent = `${weeklySuccessRate}%`;
    document.getElementById('weeklyDays').textContent = weeklyDaysWithDeliveries;
    document.getElementById('weeklyTotal').textContent = weeklyDeliveries.length;
    
    // Monthly Performance
    const monthlyDeliveries = deliveryData.filter(d => {
        const deliveryDate = normalizeDate(d.deliveryDate);
        return currentMonth.includes(deliveryDate);
    });
    
    const monthlySuccessful = monthlyDeliveries.filter(d => d.status === 'Success').length;
    const monthlySuccessRate = monthlyDeliveries.length > 0 ? ((monthlySuccessful / monthlyDeliveries.length) * 100).toFixed(1) : 0;
    
    console.log('Monthly deliveries:', monthlyDeliveries.length, 'Successful:', monthlySuccessful);
    
    document.getElementById('monthlySuccessRate').textContent = `${monthlySuccessRate}%`;
    
    // Update progress bar
    const progressBar = document.getElementById('targetProgress');
    const progressPercentage = Math.min((monthlySuccessRate / 95) * 100, 100);
    progressBar.style.width = `${progressPercentage}%`;
}

function normalizeDate(dateString) {
    if (!dateString) return '';
    
    // Handle various date formats
    if (typeof dateString === 'string') {
        // Remove time part if present
        if (dateString.includes('T')) {
            dateString = dateString.split('T')[0];
        }
        
        // Ensure YYYY-MM-DD format
        if (dateString.match(/^\d{4}-\d{2}-\d{2}$/)) {
            return dateString;
        }
        
        // Try to parse and reformat
        const date = new Date(dateString);
        if (!isNaN(date.getTime())) {
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        }
    }
    
    return '';
}

function populateTable(filterDate = null) {
    const tableBody = document.getElementById('deliveryTableBody');
    let filteredData = deliveryData;
    
    if (filterDate) {
        filteredData = deliveryData.filter(d => normalizeDate(d.deliveryDate) === filterDate);
    }
    
    if (filteredData.length === 0) {
        tableBody.innerHTML = '<tr><td colspan="10" class="loading">No delivery records found</td></tr>';
        return;
    }
    
    tableBody.innerHTML = filteredData.map(record => {
        const duration = record.duration || calculateDuration(record.dispatchTime, record.arrivalTime);
        const normalizedDate = normalizeDate(record.deliveryDate);
        
        return `
            <tr>
                <td>${formatDate(normalizedDate)}</td>
                <td>${record.invoiceNumber}</td>
                <td>${record.dispatchTime}</td>
                <td>${record.arrivalTime}</td>
                <td>${duration} min</td>
                <td><span class="status-${record.status.toLowerCase()}">${record.status}</span></td>
                <td>${record.storeSupervisor}</td>
                <td>${record.deliveryPerson}</td>
                <td>${record.vehicleNumber}</td>
                <td><button class="delete-btn" onclick="deleteRecord('${record.invoiceNumber}')">Delete</button></td>
            </tr>
        `;
    }).join('');
}

function calculateDuration(dispatchTime, arrivalTime) {
    if (!dispatchTime || !arrivalTime) return 0;
    
    try {
        const dispatch = new Date(`2000-01-01T${dispatchTime}`);
        const arrival = new Date(`2000-01-01T${arrivalTime}`);
        
        if (isNaN(dispatch.getTime()) || isNaN(arrival.getTime())) {
            return 0;
        }
        
        const diffMs = arrival - dispatch;
        return Math.round(diffMs / (1000 * 60)); // Convert to minutes
    } catch (error) {
        console.error('Error calculating duration:', error);
        return 0;
    }
}

function formatDate(dateString) {
    if (!dateString || dateString === 'Invalid Date') return 'Invalid Date';
    
    try {
        const date = new Date(dateString + 'T00:00:00'); // Add time to avoid timezone issues
        if (isNaN(date.getTime())) return 'Invalid Date';
        
        return date.toLocaleDateString('en-GB', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric'
        });
    } catch (error) {
        console.error('Error formatting date:', error);
        return 'Invalid Date';
    }
}

function getCurrentWeekDates() {
    const today = new Date();
    const currentDay = today.getDay(); // 0 = Sunday, 1 = Monday, etc.
    const mondayOffset = currentDay === 0 ? -6 : 1 - currentDay; // Get Monday of current week
    
    const monday = new Date(today);
    monday.setDate(today.getDate() + mondayOffset);
    
    const weekDates = [];
    for (let i = 0; i < 6; i++) { // Monday to Saturday
        const date = new Date(monday);
        date.setDate(monday.getDate() + i);
        weekDates.push(date.toISOString().split('T')[0]);
    }
    
    return weekDates;
}

function getCurrentMonthDates() {
    const today = new Date();
    const year = today.getFullYear();
    const month = today.getMonth();
    const daysInMonth = new Date(year, month + 1, 0).getDate();
    
    const monthDates = [];
    for (let i = 1; i <= daysInMonth; i++) {
        const date = new Date(year, month, i);
        monthDates.push(date.toISOString().split('T')[0]);
    }
    
    return monthDates;
}

async function deleteRecord(invoiceNumber) {
    if (!confirm('Are you sure you want to delete this record?')) {
        return;
    }
    
    showLoading(true);
    
    try {
        const response = await fetch(CONFIG.SHEET_URL, {
            method: 'POST',
            headers: {
                'Content-Type': 'text/plain',
            },
            body: JSON.stringify({
                action: 'deleteRecord',
                invoiceNumber: invoiceNumber
            })
        });
        
        if (response.ok) {
            const result = await response.json();
            if (result.success) {
                // Remove from local data and refresh display
                deliveryData = deliveryData.filter(record => record.invoiceNumber !== invoiceNumber);
                updateDashboard();
                populateTable();
                alert('Record deleted successfully!');
            } else {
                throw new Error(result.error || 'Failed to delete record');
            }
        } else {
            throw new Error('Network response was not ok');
        }
        
    } catch (error) {
        console.error('Error deleting record:', error);
        alert('Error deleting record: ' + error.message);
    }
    
    showLoading(false);
}

function showLoading(show) {
    if (loadingOverlay) {
        loadingOverlay.classList.toggle('hidden', !show);
    }
}

// Filter functionality
document.getElementById('filterDate').addEventListener('change', (e) => {
    const filterDate = e.target.value;
    populateTable(filterDate || null);
});

document.getElementById('clearFilterBtn').addEventListener('click', () => {
    document.getElementById('filterDate').value = '';
    populateTable();
});

// Initialize on page load
document.addEventListener('DOMContentLoaded', () => {
    // Set today's date as default for new delivery
    document.getElementById('deliveryDate').value = new Date().toISOString().split('T')[0];
});