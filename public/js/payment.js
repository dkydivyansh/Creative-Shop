document.addEventListener('DOMContentLoaded', () => {
    // --- Countdown Timer ---
    const timerElement = document.getElementById('timer');
    if (timerElement && typeof paymentExpiryTimestamp !== 'undefined' && typeof serverCurrentTimestamp !== 'undefined') {
        
        let timeLeft = paymentExpiryTimestamp - serverCurrentTimestamp;

        const timerInterval = setInterval(() => {
            if (timeLeft <= 0) {
                clearInterval(timerInterval);
                document.getElementById('razorpay-button-container').innerHTML = '<p style="color: #dc3545; font-weight: 500;">This payment session has expired.</p>';
                timerElement.textContent = '00:00';
                return;
            }

            timeLeft--; 

            const minutes = Math.floor(timeLeft / 60).toString().padStart(2, '0');
            const seconds = (timeLeft % 60).toString().padStart(2, '0');
            timerElement.textContent = `${minutes}:${seconds}`;
        }, 1000);
    }

    // --- Razorpay Integration ---
    const razorpayOptions = {
        ...razorpayConfig,
        handler: function (response) {
            processRazorpayPayment(response);
        },
        modal: {
            ondismiss: function () {
                console.log('Checkout form closed');
            }
        }
    };

    const rzp = new Razorpay(razorpayOptions);

    document.getElementById('rzp-button1').onclick = function (e) {
        rzp.open();
        e.preventDefault();
    };
});

function processRazorpayPayment(paymentResponse) {
    const errorElement = document.getElementById('payment-error');
    const buttonContainer = document.getElementById('razorpay-button-container');

    errorElement.style.display = 'none';
    buttonContainer.innerHTML = '<p>Processing your payment...</p>';

    fetch('/api/payment/verify', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            orderId: orderData.orderId,
            razorpay_payment_id: paymentResponse.razorpay_payment_id,
            razorpay_order_id: paymentResponse.razorpay_order_id,
            razorpay_signature: paymentResponse.razorpay_signature
        }),
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('The server responded with an error.');
        }
        return response.json();
    })
    .then(data => {
        if (data && data.redirectUrl) {
            window.location.href = data.redirectUrl;
        } else {
            const message = data.message || 'Payment was not successful. Please try again.';
            showPaymentError(message);
        }
    })
    .catch(error => {
        console.error('Error verifying payment:', error);
        showPaymentError('An unexpected network error occurred. Please check your connection and try again.');
    });
}

function showPaymentError(message) {
    const errorElement = document.getElementById('payment-error');
    const buttonContainer = document.getElementById('razorpay-button-container');

    errorElement.textContent = message;
    errorElement.style.display = 'block';

    buttonContainer.innerHTML = '<button id="rzp-button1" class="btn btn-primary">Pay with Razorpay</button>';
    
    // Re-attach the event listener
    const rzp = new Razorpay(razorpayOptions);
     document.getElementById('rzp-button1').onclick = function (e) {
        rzp.open();
        e.preventDefault();
    };
}