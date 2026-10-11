/* ============================================================
   Sunrise Hotel — Booking & Billing JavaScript
   Admission No: CT100/G/23988/24
   Individualized Requirement (last digit = 8):
   → Conference Room booking option at KSh 5,000
   ============================================================ */

// ---------- Rates ----------
const RATES = {
    Single: { rate: 3500, capacity: 1 },
    Double: { rate: 5000, capacity: 2 },
    Family: { rate: 7500, capacity: 4 }
};

const BREAKFAST_RATE   = 700;   // per person per day
const TRANSFER_RATE    = 2000;  // flat
const CONFERENCE_RATE  = 5000;  // flat (individualized)

// ---------- DOM References ----------
const form          = document.getElementById('bookingForm');
const fullName      = document.getElementById('fullName');
const idNumber      = document.getElementById('idNumber');
const email         = document.getElementById('email');
const phone         = document.getElementById('phone');
const checkIn       = document.getElementById('checkIn');
const nights        = document.getElementById('nights');
const roomType      = document.getElementById('roomType');
const guests        = document.getElementById('guests');
const breakfast     = document.getElementById('breakfast');
const transfer      = document.getElementById('transfer');
const conference    = document.getElementById('conference');
const roomInfo      = document.getElementById('roomInfo');
const capacityWarn  = document.getElementById('capacityWarning');
const errorBox      = document.getElementById('errorBox');

// Bill display
const roomCostEl       = document.getElementById('roomCost');
const breakfastCostEl  = document.getElementById('breakfastCost');
const transferCostEl   = document.getElementById('transferCost');
const conferenceCostEl = document.getElementById('conferenceCost');
const totalCostEl      = document.getElementById('totalCost');

// ============================================================
// FUNCTION 1: checkRoomCapacity()
// ============================================================
function checkRoomCapacity() {
    const selected = roomType.value;

    if (!selected) {
        roomInfo.textContent = '';
        capacityWarn.textContent = '';
        return;
    }

    const cap = RATES[selected].capacity;
    roomInfo.textContent = `Maximum guests for ${selected} Room: ${cap}`;

    const guestCount = parseInt(guests.value) || 0;

    if (guestCount > cap) {
        capacityWarn.textContent =
            `⚠ Warning: ${selected} Room allows a maximum of ${cap} guest(s).`;
    } else {
        capacityWarn.textContent = '';
    }
}

// ============================================================
// FUNCTION 2: calculateBill()
// ============================================================
function calculateBill() {
    const selected  = roomType.value;
    const numNights = parseInt(nights.value) || 0;
    const numGuests = parseInt(guests.value) || 0;

    let roomCost = 0;
    if (selected && RATES[selected]) {
        roomCost = RATES[selected].rate * numNights;
    }

    let breakfastCost = 0;
    if (breakfast.checked) {
        breakfastCost = BREAKFAST_RATE * numGuests * numNights;
    }

    const transferCost   = transfer.checked   ? TRANSFER_RATE   : 0;
    const conferenceCost = conference.checked ? CONFERENCE_RATE : 0;

    const total = roomCost + breakfastCost + transferCost + conferenceCost;

    roomCostEl.textContent       = `KSh ${roomCost.toLocaleString()}`;
    breakfastCostEl.textContent  = `KSh ${breakfastCost.toLocaleString()}`;
    transferCostEl.textContent   = `KSh ${transferCost.toLocaleString()}`;
    conferenceCostEl.textContent = `KSh ${conferenceCost.toLocaleString()}`;
    totalCostEl.textContent      = `KSh ${total.toLocaleString()}`;

    return total;
}

// ============================================================
// FUNCTION 3: validateBooking()
// ============================================================
function validateBooking() {
    const errors = [];

    if (fullName.value.trim() === '') {
        errors.push('Full name is required.');
    }

    if (idNumber.value.trim() === '') {
        errors.push('National ID / Passport number is required.');
    }

    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailPattern.test(email.value.trim())) {
        errors.push('Please enter a valid email address.');
    }

    const phonePattern = /^\d{10}$/;
    if (!phonePattern.test(phone.value.trim())) {
        errors.push('Phone number must contain exactly 10 digits.');
    }

    if (checkIn.value === '') {
        errors.push('Please select a check-in date.');
    }

    const numNights = parseInt(nights.value);
    if (isNaN(numNights) || numNights < 1 || numNights > 14) {
        errors.push('Number of nights must be between 1 and 14.');
    }

    const numGuests = parseInt(guests.value);
    if (isNaN(numGuests) || numGuests <= 0) {
        errors.push('Number of guests must be greater than zero.');
    }

    if (roomType.value === '') {
        errors.push('Please select a room type.');
    }

    if (roomType.value && RATES[roomType.value]) {
        const cap = RATES[roomType.value].capacity;
        if (numGuests > cap) {
            errors.push(`${roomType.value} Room allows a maximum of ${cap} guest(s).`);
        }
    }

    if (errors.length > 0) {
        errorBox.innerHTML =
            '<strong>Please correct the following:</strong><ul>' +
            errors.map(e => `<li>${e}</li>`).join('') +
            '</ul>';
        errorBox.style.display = 'block';
        window.scrollTo({ top: 0, behavior: 'smooth' });
        return false;
    }

    errorBox.style.display = 'none';
    errorBox.innerHTML = '';
    return true;
}

// ============================================================
// EVENT LISTENERS
// ============================================================
roomType.addEventListener('change', () => {
    checkRoomCapacity();
    calculateBill();
});

nights.addEventListener('change', calculateBill);
breakfast.addEventListener('change', calculateBill);
transfer.addEventListener('change', calculateBill);
conference.addEventListener('change', calculateBill);

guests.addEventListener('input', () => {
    checkRoomCapacity();
    calculateBill();
});

phone.addEventListener('input', () => {
    phone.value = phone.value.replace(/\D/g, '').slice(0, 10);
});

form.addEventListener('submit', (e) => {
    if (!validateBooking()) {
        e.preventDefault();
    }
});

document.getElementById('billSummary').addEventListener('click', calculateBill);

window.addEventListener('DOMContentLoaded', () => {
    checkIn.min = new Date().toISOString().split('T')[0];
    checkRoomCapacity();
    calculateBill();
});