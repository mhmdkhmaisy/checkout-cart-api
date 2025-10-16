@extends('layouts.public')

@section('title', 'Terms & Conditions - Aragon RSPS')
@section('description', 'Terms of Service and Refund Policy for Aragon RSPS Store')

@section('content')
<style>
.terms-container {
    display: grid;
    grid-template-columns: 280px 1fr;
    gap: 2rem;
    margin-top: 2rem;
    max-width: 1400px;
    margin-left: auto;
    margin-right: auto;
}

.terms-sidebar {
    position: sticky;
    top: 90px;
    height: fit-content;
    background: rgba(10, 10, 10, 0.98);
    border: 1px solid var(--border-color);
    border-radius: 8px;
    padding: 1.5rem;
}

.terms-sidebar h3 {
    font-size: 0.9rem;
    font-weight: 700;
    color: var(--primary-color);
    text-transform: uppercase;
    margin-bottom: 1rem;
    letter-spacing: 0.5px;
}

.toc-item {
    display: block;
    padding: 0.6rem 0.75rem;
    color: var(--text-muted);
    text-decoration: none;
    border-radius: 6px;
    transition: all 0.2s ease;
    font-size: 0.85rem;
    margin-bottom: 0.25rem;
}

.toc-item:hover {
    background: rgba(212, 0, 0, 0.1);
    color: var(--text-light);
    padding-left: 1rem;
}

.toc-item.active {
    background: rgba(212, 0, 0, 0.15);
    color: var(--primary-color);
    font-weight: 600;
}

.terms-content {
    background: rgba(10, 10, 10, 0.98);
    border: 1px solid var(--border-color);
    border-radius: 8px;
    padding: 2.5rem;
    min-height: 70vh;
}

.terms-content h1 {
    font-size: 2rem;
    font-weight: 800;
    color: var(--text-light);
    margin-bottom: 0.5rem;
}

.terms-content .subtitle {
    color: var(--text-muted);
    margin-bottom: 2rem;
    font-size: 0.95rem;
}

.terms-content h2 {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--primary-color);
    margin-top: 2.5rem;
    margin-bottom: 1rem;
    padding-bottom: 0.5rem;
    border-bottom: 2px solid var(--border-color);
}

.terms-content h3 {
    font-size: 1.15rem;
    font-weight: 600;
    color: var(--text-light);
    margin-top: 1.5rem;
    margin-bottom: 0.75rem;
}

.terms-content p {
    color: var(--text-muted);
    line-height: 1.7;
    margin-bottom: 1rem;
}

.terms-content ul, .terms-content ol {
    color: var(--text-muted);
    line-height: 1.7;
    margin-bottom: 1rem;
    padding-left: 1.5rem;
}

.terms-content li {
    margin-bottom: 0.5rem;
}

.terms-content strong {
    color: var(--text-light);
    font-weight: 600;
}

.highlight-box {
    background: rgba(212, 0, 0, 0.1);
    border-left: 3px solid var(--primary-color);
    padding: 1rem 1.25rem;
    margin: 1.5rem 0;
    border-radius: 4px;
}

.highlight-box p {
    margin-bottom: 0;
    color: var(--text-light);
}

@media (max-width: 992px) {
    .terms-container {
        grid-template-columns: 1fr;
    }
    
    .terms-sidebar {
        position: static;
        margin-bottom: 1.5rem;
    }
}
</style>

<div class="container">
    <div class="terms-container">
        <!-- Sidebar Table of Contents -->
        <aside class="terms-sidebar">
            <h3><i class="fas fa-list-ul"></i> Table of Contents</h3>
            <nav>
                <a href="#introduction" class="toc-item active">Introduction</a>
                <a href="#acceptance" class="toc-item">Acceptance of Terms</a>
                <a href="#purchases" class="toc-item">Virtual Purchases</a>
                <a href="#refund-policy" class="toc-item">Refund Policy</a>
                <a href="#account" class="toc-item">Account Responsibility</a>
                <a href="#prohibited" class="toc-item">Prohibited Activities</a>
                <a href="#termination" class="toc-item">Account Termination</a>
                <a href="#liability" class="toc-item">Limitation of Liability</a>
                <a href="#changes" class="toc-item">Changes to Terms</a>
                <a href="#contact" class="toc-item">Contact Information</a>
            </nav>
        </aside>

        <!-- Main Content -->
        <div class="terms-content">
            <h1>Terms & Conditions</h1>
            <p class="subtitle">Last Updated: {{ date('F d, Y') }}</p>

            <section id="introduction">
                <h2>1. Introduction</h2>
                <p>Welcome to Aragon RSPS ("the Server"). By accessing or using our services, including our website and game server, you agree to be bound by these Terms and Conditions. Please read them carefully before making any purchases or using our services.</p>
                <p>Aragon RSPS is a private RuneScape server operated independently and is not affiliated with, endorsed by, or associated with Jagex Ltd. or RuneScape®.</p>
            </section>

            <section id="acceptance">
                <h2>2. Acceptance of Terms</h2>
                <p>By creating an account, making a purchase, or using any part of our service, you acknowledge that you have read, understood, and agree to be bound by these Terms and Conditions and our Privacy Policy.</p>
                <p>If you do not agree to these terms, you must not use our services or make any purchases.</p>
            </section>

            <section id="purchases">
                <h2>3. Virtual Purchases & Digital Goods</h2>
                
                <h3>3.1 Virtual Items</h3>
                <p>All items, currency, and services available for purchase in our store are virtual goods that exist solely within the Aragon RSPS game environment. These items have no real-world monetary value and cannot be exchanged for cash or real-world goods.</p>

                <h3>3.2 Payment Processing</h3>
                <p>We accept payments through authorized payment processors including PayPal and Coinbase. By making a purchase, you agree to the terms and conditions of the respective payment provider.</p>

                <h3>3.3 Delivery</h3>
                <p>Virtual items are typically delivered to your in-game account within 24 hours of payment confirmation. In rare cases, delivery may take up to 48 hours. If you do not receive your items within this timeframe, please contact our support team.</p>

                <h3>3.4 Pricing</h3>
                <p>All prices are listed in USD and are subject to change at any time without prior notice. Prices displayed at the time of purchase are final.</p>
            </section>

            <section id="refund-policy">
                <h2>4. Refund Policy</h2>
                
                <div class="highlight-box">
                    <p><strong><i class="fas fa-exclamation-triangle"></i> IMPORTANT: ALL SALES ARE FINAL</strong></p>
                </div>

                <h3>4.1 No Refunds</h3>
                <p>Due to the digital nature of our products and services, <strong>all purchases are final and non-refundable</strong>. We do not offer refunds, exchanges, or store credit under any circumstances, including but not limited to:</p>
                <ul>
                    <li>Change of mind or buyer's remorse</li>
                    <li>Account suspension or termination due to rule violations</li>
                    <li>Server wipes, resets, or economy changes</li>
                    <li>Loss of items due to gameplay, bugs, or glitches</li>
                    <li>Inability to access the server or your account</li>
                    <li>Dissatisfaction with purchased items or services</li>
                    <li>Technical issues on your end (internet connection, device problems, etc.)</li>
                </ul>

                <h3>4.2 Exceptions</h3>
                <p>The only circumstances under which a refund may be considered are:</p>
                <ul>
                    <li>Duplicate charges due to payment processing errors (must be reported within 48 hours)</li>
                    <li>Failure to receive purchased items due to our server errors (after exhausting all support options)</li>
                    <li>Unauthorized charges (requires proof and immediate report to support)</li>
                </ul>

                <h3>4.3 Chargebacks</h3>
                <p><strong>WARNING:</strong> Filing a chargeback or dispute without attempting to resolve the issue with our support team will result in immediate and permanent account termination. All items, currency, and progress associated with the account will be forfeited.</p>
            </section>

            <section id="account">
                <h2>5. Account Responsibility</h2>
                <p>You are solely responsible for:</p>
                <ul>
                    <li>Maintaining the confidentiality of your account credentials</li>
                    <li>All activities that occur under your account</li>
                    <li>Ensuring your account information is accurate and up-to-date</li>
                    <li>Any purchases made using your account</li>
                </ul>
                <p>We are not responsible for any loss or damage resulting from unauthorized access to your account due to your failure to keep your credentials secure.</p>
            </section>

            <section id="prohibited">
                <h2>6. Prohibited Activities</h2>
                <p>The following activities are strictly prohibited and may result in account termination without refund:</p>
                <ul>
                    <li>Using third-party software, bots, or macros</li>
                    <li>Real-world trading (RWT) of virtual items or currency</li>
                    <li>Scamming, fraud, or deceptive practices</li>
                    <li>Harassment, hate speech, or abusive behavior</li>
                    <li>Exploiting bugs or glitches for personal gain</li>
                    <li>Sharing or selling accounts</li>
                    <li>Chargebacks or payment fraud</li>
                </ul>
            </section>

            <section id="termination">
                <h2>7. Account Termination</h2>
                <p>We reserve the right to suspend or terminate your account at any time, with or without cause, and without prior notice. Reasons for termination may include, but are not limited to:</p>
                <ul>
                    <li>Violation of these Terms and Conditions</li>
                    <li>Violation of server rules or policies</li>
                    <li>Fraudulent activity or chargebacks</li>
                    <li>Abusive or disruptive behavior</li>
                </ul>
                <p><strong>No refunds will be issued for terminated accounts, regardless of the reason for termination.</strong></p>
            </section>

            <section id="liability">
                <h2>8. Limitation of Liability</h2>
                <p>Aragon RSPS and its operators shall not be liable for:</p>
                <ul>
                    <li>Any loss of virtual items, currency, or account progress</li>
                    <li>Server downtime, maintenance, or technical issues</li>
                    <li>Changes to game mechanics, items, or economy</li>
                    <li>Any indirect, incidental, or consequential damages</li>
                    <li>Loss of profits or revenue</li>
                </ul>
                <p>Our maximum liability is limited to the amount you paid for the specific purchase in question.</p>
            </section>

            <section id="changes">
                <h2>9. Changes to Terms</h2>
                <p>We reserve the right to modify these Terms and Conditions at any time. Changes will be effective immediately upon posting to our website. Your continued use of our services after any changes constitutes acceptance of the modified terms.</p>
                <p>It is your responsibility to review these terms periodically for updates.</p>
            </section>

            <section id="contact">
                <h2>10. Contact Information</h2>
                <p>If you have any questions about these Terms and Conditions or need support, please contact us:</p>
                <ul>
                    <li><strong>Discord:</strong> Join our community server</li>
                    <li><strong>In-Game:</strong> Contact a staff member</li>
                    <li><strong>Support Ticket:</strong> Submit through our website</li>
                </ul>
                <p style="margin-top: 2rem; padding-top: 2rem; border-top: 1px solid var(--border-color); font-size: 0.85rem; opacity: 0.7;">
                    By using our services, you acknowledge that you have read and understood these Terms and Conditions and agree to be bound by them.
                </p>
            </section>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Smooth scroll to sections
document.querySelectorAll('.toc-item').forEach(link => {
    link.addEventListener('click', function(e) {
        e.preventDefault();
        const targetId = this.getAttribute('href').substring(1);
        const targetSection = document.getElementById(targetId);
        
        if (targetSection) {
            targetSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
            
            // Update active state
            document.querySelectorAll('.toc-item').forEach(item => item.classList.remove('active'));
            this.classList.add('active');
        }
    });
});

// Update active section on scroll
let sections = document.querySelectorAll('section[id]');
let tocItems = document.querySelectorAll('.toc-item');

window.addEventListener('scroll', () => {
    let current = '';
    
    sections.forEach(section => {
        const sectionTop = section.offsetTop;
        const sectionHeight = section.clientHeight;
        if (window.pageYOffset >= (sectionTop - 150)) {
            current = section.getAttribute('id');
        }
    });

    tocItems.forEach(item => {
        item.classList.remove('active');
        if (item.getAttribute('href') === '#' + current) {
            item.classList.add('active');
        }
    });
});
</script>
@endpush
@endsection
