<?php

return [
    'categories' => [
        [
            'key' => 'identity',
            'label' => 'Identity & Trust',
            'description' => 'Identity verification and compliance-grade checks.',
        ],
        [
            'key' => 'developer',
            'label' => 'Developer APIs',
            'description' => 'Public API, webhooks, and integration tooling.',
        ],
        [
            'key' => 'legal',
            'label' => 'Legal & Notary',
            'description' => 'AI legal drafting and document workflows.',
        ],
        [
            'key' => 'commerce',
            'label' => 'Auctions & Marketplace',
            'description' => 'Asset listings and transparent bidding.',
        ],
        [
            'key' => 'finance',
            'label' => 'Finance & Payments',
            'description' => 'Agency banking, invoicing, and business finance tools.',
        ],
        [
            'key' => 'logistics',
            'label' => 'Logistics',
            'description' => 'Shipping, tracking, and delivery services.',
        ],
        [
            'key' => 'education',
            'label' => 'Education',
            'description' => 'Pins and verification for education services.',
        ],
        [
            'key' => 'insurance',
            'label' => 'Insurance',
            'description' => 'Insurance purchase and verification flows.',
        ],
    ],
    'services' => [
        [
            'slug' => 'identity-verification',
            'title' => 'Identity Verification',
            'category' => 'identity',
            'icon' => 'fa-solid fa-id-card',
            'tagline' => 'Verify identities with audit-ready results.',
            'summary' => 'NIN, BVN, CAC, Driver’s License, Passport, TIN, and more — with provider suites, type-based pricing, and downloadable reports.',
            'highlights' => [
                'Multiple providers per suite',
                'Provider-specific verification types',
                'Verification history & PDF reports',
            ],
            'cta' => [
                'primary_label' => 'Create account to verify',
                'secondary_label' => 'View pricing',
            ],
            'links' => [
                'primary' => '/register',
                'secondary' => '/services/price-list',
            ],
            'image' => [
                'gradient' => 'linear-gradient(135deg, rgba(59,130,246,0.20), rgba(16,185,129,0.10))',
            ],
        ],
        [
            'slug' => 'bvn-verification',
            'title' => 'BVN Verification',
            'category' => 'identity',
            'icon' => 'fa-solid fa-fingerprint',
            'tagline' => 'Biometric identity checks with audit trails.',
            'summary' => 'Instant biometric matching and financial identity validation with bank-grade security and downloadable results.',
            'highlights' => [
                'Basic / premium types per provider',
                'Provider suites and failover',
                'Audit-ready history and reports',
            ],
            'cta' => [
                'primary_label' => 'Create account to verify',
                'secondary_label' => 'View pricing',
            ],
            'links' => [
                'primary' => '/register',
                'secondary' => '/services/price-list',
            ],
            'image' => [
                'gradient' => 'linear-gradient(135deg, rgba(59,130,246,0.22), rgba(139,92,246,0.10))',
            ],
        ],
        [
            'slug' => 'validation',
            'title' => 'Document Validation',
            'category' => 'identity',
            'icon' => 'fa-solid fa-file-shield',
            'tagline' => 'Official document authenticity checks.',
            'summary' => 'Verify the authenticity of various identity documents and official records with automated cross-referencing and verification reports.',
            'highlights' => [
                'Instant validation',
                'Multi-document support',
                'Authenticity reports',
            ],
            'cta' => [
                'primary_label' => 'Create account to validate',
                'secondary_label' => 'View pricing',
            ],
            'links' => [
                'primary' => '/register',
                'secondary' => '/services/price-list',
            ],
            'image' => [
                'gradient' => 'linear-gradient(135deg, rgba(16,185,129,0.20), rgba(59,130,246,0.10))',
            ],
        ],
        [
            'slug' => 'clearance',
            'title' => 'IPE Clearance',
            'category' => 'identity',
            'icon' => 'fa-solid fa-user-check',
            'tagline' => 'Clearance and vetting services.',
            'summary' => 'Comprehensive background vetting and clearance checks for individuals and entities with IPE-standard compliance.',
            'highlights' => [
                'Detailed vetting',
                'IPE-standard reports',
                'Fast turnaround',
            ],
            'cta' => [
                'primary_label' => 'Create account for clearance',
                'secondary_label' => 'View pricing',
            ],
            'links' => [
                'primary' => '/register',
                'secondary' => '/services/price-list',
            ],
            'image' => [
                'gradient' => 'linear-gradient(135deg, rgba(245,158,11,0.20), rgba(16,185,129,0.10))',
            ],
        ],
        [
            'slug' => 'personalization',
            'title' => 'Personalization',
            'category' => 'identity',
            'icon' => 'fa-solid fa-user-gear',
            'tagline' => 'Tailored identity services.',
            'summary' => 'Advanced identity personalization and profile management for verified individuals and business accounts.',
            'highlights' => [
                'Custom identity profiles',
                'Advanced management',
                'Verified credentials',
            ],
            'cta' => [
                'primary_label' => 'Create account',
                'secondary_label' => 'View pricing',
            ],
            'links' => [
                'primary' => '/register',
                'secondary' => '/services/price-list',
            ],
            'image' => [
                'gradient' => 'linear-gradient(135deg, rgba(139,92,246,0.20), rgba(245,158,11,0.10))',
            ],
        ],
        [
            'slug' => 'nin-verification',
            'title' => 'NIN Verification',
            'category' => 'identity',
            'icon' => 'fa-solid fa-id-card',
            'tagline' => 'Real-time NIN validation for compliance.',
            'summary' => 'Validate NIN and match identity fields with provider suites and transparent pricing.',
            'highlights' => [
                'NIN by number or phone modes',
                'Provider-specific pricing types',
                'Verification history and references',
            ],
            'cta' => [
                'primary_label' => 'Create account to verify',
                'secondary_label' => 'View pricing',
            ],
            'links' => [
                'primary' => '/register',
                'secondary' => '/services/price-list',
            ],
            'image' => [
                'gradient' => 'linear-gradient(135deg, rgba(16,185,129,0.16), rgba(59,130,246,0.12))',
            ],
        ],
        [
            'slug' => 'drivers-license-check',
            'title' => 'Driver’s License Check',
            'category' => 'identity',
            'icon' => 'fa-solid fa-car',
            'tagline' => 'DL verification for transport-grade onboarding.',
            'summary' => 'Verify driver’s license credentials with provider suites and type-based pricing.',
            'highlights' => [
                'Provider selection per verification',
                'Type-specific pricing',
                'Saved verification results',
            ],
            'cta' => [
                'primary_label' => 'Create account to verify',
                'secondary_label' => 'View pricing',
            ],
            'links' => [
                'primary' => '/register',
                'secondary' => '/services/price-list',
            ],
            'image' => [
                'gradient' => 'linear-gradient(135deg, rgba(245,158,11,0.16), rgba(59,130,246,0.10))',
            ],
        ],
        [
            'slug' => 'cac-verification',
            'title' => 'CAC Business Verification',
            'category' => 'identity',
            'icon' => 'fa-solid fa-building',
            'tagline' => 'Validate company details fast.',
            'summary' => 'Verify RC numbers and business details via provider suites and transparent pricing.',
            'highlights' => [
                'Provider selection',
                'Type-based pricing per provider',
                'Downloadable results',
            ],
            'cta' => [
                'primary_label' => 'Create account to verify',
                'secondary_label' => 'View pricing',
            ],
            'links' => [
                'primary' => '/register',
                'secondary' => '/services/price-list',
            ],
            'image' => [
                'gradient' => 'linear-gradient(135deg, rgba(139,92,246,0.16), rgba(16,185,129,0.10))',
            ],
        ],
        [
            'slug' => 'address-verification',
            'title' => 'Address Verification',
            'category' => 'identity',
            'icon' => 'fa-solid fa-location-dot',
            'tagline' => 'Verify addresses with traceability.',
            'summary' => 'Submit and track address verification requests with status history and references.',
            'highlights' => [
                'Marketplace identity matching',
                'Track status updates',
                'View detailed reports',
            ],
            'cta' => [
                'primary_label' => 'Create account to verify',
                'secondary_label' => 'View pricing',
            ],
            'links' => [
                'primary' => '/register',
                'secondary' => '/services/price-list',
            ],
            'image' => [
                'gradient' => 'linear-gradient(135deg, rgba(59,130,246,0.16), rgba(245,158,11,0.10))',
            ],
        ],
        [
            'slug' => 'passport-verification',
            'title' => 'Passport Verification',
            'category' => 'identity',
            'icon' => 'fa-solid fa-passport',
            'tagline' => 'Passport checks for higher-trust onboarding.',
            'summary' => 'Verify passport details with provider suites and saved verification results.',
            'highlights' => [
                'Provider selection',
                'Audit-ready records',
                'Exportable reports',
            ],
            'cta' => [
                'primary_label' => 'Create account to verify',
                'secondary_label' => 'View pricing',
            ],
            'links' => [
                'primary' => '/register',
                'secondary' => '/services/price-list',
            ],
            'image' => [
                'gradient' => 'linear-gradient(135deg, rgba(59,130,246,0.14), rgba(139,92,246,0.10))',
            ],
        ],
        [
            'slug' => 'tin-verification',
            'title' => 'TIN Verification',
            'category' => 'identity',
            'icon' => 'fa-solid fa-receipt',
            'tagline' => 'Tax identity validation for compliance.',
            'summary' => 'Validate TIN details with provider suites and type-based pricing.',
            'highlights' => [
                'Provider selection',
                'Type-based pricing',
                'Saved verification history',
            ],
            'cta' => [
                'primary_label' => 'Create account to verify',
                'secondary_label' => 'View pricing',
            ],
            'links' => [
                'primary' => '/register',
                'secondary' => '/services/price-list',
            ],
            'image' => [
                'gradient' => 'linear-gradient(135deg, rgba(16,185,129,0.14), rgba(245,158,11,0.10))',
            ],
        ],
        [
            'slug' => 'voters-card-verification',
            'title' => 'Voter’s Card Verification',
            'category' => 'identity',
            'icon' => 'fa-solid fa-id-badge',
            'tagline' => 'Verify voter’s card records.',
            'summary' => 'Verify voter’s card details with provider suites and auditable results.',
            'highlights' => [
                'Provider selection',
                'Saved results',
                'Pricing by provider',
            ],
            'cta' => [
                'primary_label' => 'Create account to verify',
                'secondary_label' => 'View pricing',
            ],
            'links' => [
                'primary' => '/register',
                'secondary' => '/services/price-list',
            ],
            'image' => [
                'gradient' => 'linear-gradient(135deg, rgba(236,72,153,0.12), rgba(59,130,246,0.10))',
            ],
        ],
        [
            'slug' => 'biometric-verification',
            'title' => 'Biometric Verification',
            'category' => 'identity',
            'icon' => 'fa-solid fa-user-check',
            'tagline' => 'Biometric checks for fraud prevention.',
            'summary' => 'Run biometric verification workflows and store auditable results.',
            'highlights' => [
                'Provider-based verification',
                'Type-specific pricing',
                'Result storage and reports',
            ],
            'cta' => [
                'primary_label' => 'Create account to verify',
                'secondary_label' => 'View pricing',
            ],
            'links' => [
                'primary' => '/register',
                'secondary' => '/services/price-list',
            ],
            'image' => [
                'gradient' => 'linear-gradient(135deg, rgba(16,185,129,0.16), rgba(139,92,246,0.10))',
            ],
        ],
        [
            'slug' => 'plate-number-verification',
            'title' => 'Plate Number Verification',
            'category' => 'identity',
            'icon' => 'fa-solid fa-car-side',
            'tagline' => 'Vehicle identity checks.',
            'summary' => 'Verify vehicle details by plate number with provider suites.',
            'highlights' => [
                'Provider selection',
                'Saved results',
                'Transparent pricing',
            ],
            'cta' => [
                'primary_label' => 'Create account to verify',
                'secondary_label' => 'View pricing',
            ],
            'links' => [
                'primary' => '/register',
                'secondary' => '/services/price-list',
            ],
            'image' => [
                'gradient' => 'linear-gradient(135deg, rgba(59,130,246,0.12), rgba(245,158,11,0.10))',
            ],
        ],
        [
            'slug' => 'stamp-duty-verification',
            'title' => 'Stamp Duty Verification',
            'category' => 'identity',
            'icon' => 'fa-solid fa-stamp',
            'tagline' => 'Stamp duty checks for business compliance.',
            'summary' => 'Validate stamp duty information with provider suites and auditable records.',
            'highlights' => [
                'Provider selection',
                'Type-specific pricing',
                'Verification history',
            ],
            'cta' => [
                'primary_label' => 'Create account to verify',
                'secondary_label' => 'View pricing',
            ],
            'links' => [
                'primary' => '/register',
                'secondary' => '/services/price-list',
            ],
            'image' => [
                'gradient' => 'linear-gradient(135deg, rgba(245,158,11,0.14), rgba(139,92,246,0.10))',
            ],
        ],
        [
            'slug' => 'credit-bureau-check',
            'title' => 'Credit Bureau Check',
            'category' => 'identity',
            'icon' => 'fa-solid fa-chart-line',
            'tagline' => 'Risk signals for credit and lending.',
            'summary' => 'Credit bureau verification flows for operational risk checks and lending decisions.',
            'highlights' => [
                'Provider selection',
                'Saved results',
                'Pricing by provider',
            ],
            'cta' => [
                'primary_label' => 'Create account to verify',
                'secondary_label' => 'View pricing',
            ],
            'links' => [
                'primary' => '/register',
                'secondary' => '/services/price-list',
            ],
            'image' => [
                'gradient' => 'linear-gradient(135deg, rgba(59,130,246,0.14), rgba(16,185,129,0.10))',
            ],
        ],
        [
            'slug' => 'developer-api',
            'title' => 'Developer API',
            'category' => 'developer',
            'icon' => 'fa-solid fa-code',
            'tagline' => 'Integrate verification into your app.',
            'summary' => 'Token auth, rate limiting, OpenAPI spec, and a developer portal for self-serve API keys and integration snippets.',
            'highlights' => [
                'Bearer tokens + per-token rate limits',
                'OpenAPI specification',
                'Webhook signature enforcement',
            ],
            'cta' => [
                'primary_label' => 'Get an API token',
                'secondary_label' => 'Download OpenAPI',
            ],
            'links' => [
                'primary' => '/login',
                'secondary' => '/developer/openapi/v1',
            ],
            'image' => [
                'gradient' => 'linear-gradient(135deg, rgba(139,92,246,0.18), rgba(59,130,246,0.12))',
            ],
        ],
        [
            'slug' => 'ai-legal-hub',
            'title' => 'AI Legal Hub',
            'category' => 'legal',
            'icon' => 'fa-solid fa-brain',
            'tagline' => 'Draft legal documents in seconds.',
            'summary' => 'Create compliant legal drafts and document workflows aligned to your business process.',
            'highlights' => [
                'Structured drafting workflows',
                'Templates and previews',
                'Controlled document access',
            ],
            'cta' => [
                'primary_label' => 'Start drafting',
                'secondary_label' => 'Create account',
            ],
            'links' => [
                'primary' => '/login',
                'secondary' => '/register',
            ],
            'image' => [
                'gradient' => 'linear-gradient(135deg, rgba(16,185,129,0.12), rgba(245,158,11,0.10))',
            ],
        ],
        [
            'slug' => 'auctions',
            'title' => 'Auctions',
            'category' => 'commerce',
            'icon' => 'fa-solid fa-gavel',
            'tagline' => 'Browse auctions like a pro bidding platform.',
            'summary' => 'Search and sort live auctions, view lots with image galleries, timelines, and bid history. Participation is available after login.',
            'highlights' => [
                'Filtering and sorting',
                'Lot details + bid history',
                'Countdown timers',
            ],
            'cta' => [
                'primary_label' => 'Browse live auctions',
                'secondary_label' => 'Sign in to bid',
            ],
            'links' => [
                'primary' => '/explore/auctions',
                'secondary' => '/login',
            ],
            'image' => [
                'gradient' => 'linear-gradient(135deg, rgba(236,72,153,0.16), rgba(245,158,11,0.10))',
            ],
        ],
        [
            'slug' => 'logistics',
            'title' => 'Logistics',
            'category' => 'logistics',
            'icon' => 'fa-solid fa-truck-fast',
            'tagline' => 'Book shipments and track deliveries.',
            'summary' => 'Logistics workflows for dispatch, tracking, and status history. Actions are available after login.',
            'highlights' => [
                'Shipment booking',
                'Tracking and status updates',
                'Receipts and references',
            ],
            'cta' => [
                'primary_label' => 'Create account',
                'secondary_label' => 'Sign in',
            ],
            'links' => [
                'primary' => '/register',
                'secondary' => '/login',
            ],
            'image' => [
                'gradient' => 'linear-gradient(135deg, rgba(59,130,246,0.14), rgba(245,158,11,0.10))',
            ],
        ],
        [
            'slug' => 'finance',
            'title' => 'Finance & Payments',
            'category' => 'finance',
            'icon' => 'fa-solid fa-building-columns',
            'tagline' => 'Run essential finance operations.',
            'summary' => 'Agency banking, invoicing, FX, virtual cards, and operational tooling in one dashboard.',
            'highlights' => [
                'Invoicing and history',
                'Virtual cards and funding',
                'Operational controls',
            ],
            'cta' => [
                'primary_label' => 'Get started',
                'secondary_label' => 'View pricing',
            ],
            'links' => [
                'primary' => '/register',
                'secondary' => '/services/price-list',
            ],
            'image' => [
                'gradient' => 'linear-gradient(135deg, rgba(16,185,129,0.14), rgba(59,130,246,0.12))',
            ],
        ],
        [
            'slug' => 'agency-banking',
            'title' => 'Agency Banking',
            'category' => 'finance',
            'icon' => 'fa-solid fa-building-columns',
            'tagline' => 'Operate an agent network from one dashboard.',
            'summary' => 'Connect agents, process transfers, and control cash flows across your POS network.',
            'highlights' => [
                'Agent requests and workflows',
                'Operational visibility',
                'Role-based access',
            ],
            'cta' => [
                'primary_label' => 'Create account',
                'secondary_label' => 'Sign in',
            ],
            'links' => [
                'primary' => '/register',
                'secondary' => '/login',
            ],
            'image' => [
                'gradient' => 'linear-gradient(135deg, rgba(16,185,129,0.14), rgba(245,158,11,0.10))',
            ],
        ],
        [
            'slug' => 'virtual-cards',
            'title' => 'Virtual Cards',
            'category' => 'finance',
            'icon' => 'fa-solid fa-credit-card',
            'tagline' => 'Instant USD & NGN virtual cards with spend controls.',
            'summary' => 'Create and fund virtual cards for subscriptions and online payments with operational controls.',
            'highlights' => [
                'Card creation',
                'Funding workflows',
                'Spend controls',
            ],
            'cta' => [
                'primary_label' => 'Create account',
                'secondary_label' => 'Sign in',
            ],
            'links' => [
                'primary' => '/register',
                'secondary' => '/login',
            ],
            'image' => [
                'gradient' => 'linear-gradient(135deg, rgba(59,130,246,0.14), rgba(139,92,246,0.10))',
            ],
        ],
        [
            'slug' => 'fx-exchange',
            'title' => 'FX & Currency Conversion',
            'category' => 'finance',
            'icon' => 'fa-solid fa-arrow-right-arrow-left',
            'tagline' => 'Exchange with live rates and operational control.',
            'summary' => 'Live NGN, USD, GBP, and EUR rates with exchange workflows built for global businesses.',
            'highlights' => [
                'Live rates',
                'Exchange workflows',
                'Operational visibility',
            ],
            'cta' => [
                'primary_label' => 'Create account',
                'secondary_label' => 'Sign in',
            ],
            'links' => [
                'primary' => '/register',
                'secondary' => '/login',
            ],
            'image' => [
                'gradient' => 'linear-gradient(135deg, rgba(245,158,11,0.14), rgba(59,130,246,0.10))',
            ],
        ],
        [
            'slug' => 'invoicing',
            'title' => 'Invoicing & Subscriptions',
            'category' => 'finance',
            'icon' => 'fa-solid fa-file-invoice',
            'tagline' => 'Recurring billing and branded invoices.',
            'summary' => 'Create invoices and manage billing workflows with reminders and payment references.',
            'highlights' => [
                'Invoice creation',
                'Operational tracking',
                'Admin operations panel',
            ],
            'cta' => [
                'primary_label' => 'Create account',
                'secondary_label' => 'Sign in',
            ],
            'links' => [
                'primary' => '/register',
                'secondary' => '/login',
            ],
            'image' => [
                'gradient' => 'linear-gradient(135deg, rgba(16,185,129,0.12), rgba(139,92,246,0.10))',
            ],
        ],
        [
            'slug' => 'vtu-services',
            'title' => 'VTU & Bill Payments',
            'category' => 'finance',
            'icon' => 'fa-solid fa-layer-group',
            'tagline' => 'Pay bills fast with a single wallet.',
            'summary' => 'Airtime, data, cable TV, and electricity payments—fast checkout, trackable references, and a clean transaction history.',
            'highlights' => [
                'Airtime, data, cable, electricity',
                'Wallet billing and references',
                'History for reconciliation',
            ],
            'cta' => [
                'primary_label' => 'Create account to pay bills',
                'secondary_label' => 'Sign in',
            ],
            'links' => [
                'primary' => '/register',
                'secondary' => '/login',
            ],
            'landing' => [
                'hero' => [
                    'headline_A' => 'Pay bills in minutes—airtime, data, cable, electricity.',
                    'headline_B' => 'Stop failed payments. Use one wallet for every VTU bill.',
                    'subheadline' => 'A VTU-style experience built for speed, proof-of-payment, and clean reconciliation—whether you’re buying for yourself or for customers.',
                    'primary_cta_label' => 'Create free account',
                    'secondary_cta_label' => 'Sign in',
                ],
                'pain_points' => [
                    [
                        'title' => 'Failed payments and repeated retries',
                        'body' => 'Nothing kills trust like a “debited but not delivered” experience.',
                    ],
                    [
                        'title' => 'Hard-to-track receipts',
                        'body' => 'Without references and history, support and accounting become a mess.',
                    ],
                    [
                        'title' => 'Too many apps',
                        'body' => 'Switching platforms slows you down and increases mistakes.',
                    ],
                ],
                'benefits' => [
                    [
                        'title' => 'Fast checkout',
                        'body' => 'Get in, pay, and move on—built for urgent needs.',
                        'icon' => 'fa-solid fa-bolt',
                    ],
                    [
                        'title' => 'Trackable references',
                        'body' => 'Every transaction has a record you can search later.',
                        'icon' => 'fa-solid fa-receipt',
                    ],
                    [
                        'title' => 'Single wallet',
                        'body' => 'Fund once and pay across VTU categories.',
                        'icon' => 'fa-solid fa-wallet',
                    ],
                    [
                        'title' => 'Built for resellers',
                        'body' => 'Designed to reduce errors and speed up repeat purchases.',
                        'icon' => 'fa-solid fa-store',
                    ],
                ],
                'modules' => [
                    [
                        'title' => 'Airtime',
                        'subtitle' => 'MTN, Glo, Airtel, 9mobile',
                        'icon' => 'fa-solid fa-mobile-screen-button',
                        'link' => '/explore/airtime',
                    ],
                    [
                        'title' => 'Data Bundles',
                        'subtitle' => 'Multi-network plans',
                        'icon' => 'fa-solid fa-wifi',
                        'link' => '/explore/data-bundles',
                    ],
                    [
                        'title' => 'Cable TV',
                        'subtitle' => 'DSTV, GOTV, Startimes',
                        'icon' => 'fa-solid fa-tv',
                        'link' => '/explore/cable-tv',
                    ],
                    [
                        'title' => 'Electricity',
                        'subtitle' => 'IBEDC, EKEDC, IKEDC and more',
                        'icon' => 'fa-solid fa-lightbulb',
                        'link' => '/explore/electricity-bills',
                    ],
                    [
                        'title' => 'Airtime to Cash',
                        'subtitle' => 'Convert airtime value',
                        'icon' => 'fa-solid fa-money-bill-transfer',
                        'link' => '/explore/vtu-services',
                        'badge' => 'Coming soon',
                    ],
                ],
                'steps' => [
                    ['title' => 'Create an account', 'body' => 'Unlock bill payments and keep a record of every transaction.'],
                    ['title' => 'Fund your wallet', 'body' => 'Add funds once and pay across services.'],
                    ['title' => 'Pay and track', 'body' => 'Complete payments and keep references in history.'],
                ],
                'faq' => [
                    [
                        'q' => 'Is VTU available without login?',
                        'a' => 'You can browse the public pages, but transactions require login to protect billing and prevent abuse.',
                    ],
                    [
                        'q' => 'Does this include electricity and cable payments?',
                        'a' => 'Yes. Electricity and cable are part of the VTU hub inside the app after you sign in.',
                    ],
                ],
                'closing' => [
                    'headline' => 'Make bill payments boring—in the best way.',
                    'body' => 'Create an account to unlock a VTU hub designed for speed, trust, and trackable references.',
                ],
            ],
            'image' => [
                'gradient' => 'linear-gradient(135deg, rgba(59,130,246,0.16), rgba(139,92,246,0.12))',
            ],
        ],
        [
            'slug' => 'airtime',
            'title' => 'VTU Airtime',
            'category' => 'finance',
            'icon' => 'fa-solid fa-mobile-screen-button',
            'tagline' => 'Instant airtime top-ups that keep you connected.',
            'summary' => 'Top up in seconds, avoid failed recharges, and keep clean references for every purchase.',
            'highlights' => [
                'Fast, reliable fulfillment',
                'Wallet billing with references',
                'Receipts via transaction history',
            ],
            'cta' => [
                'primary_label' => 'Create account',
                'secondary_label' => 'Sign in',
            ],
            'links' => [
                'primary' => '/register',
                'secondary' => '/login',
            ],
            'landing' => [
                'hero' => [
                    'headline_A' => 'Top up airtime in seconds—stay connected when it matters.',
                    'headline_B' => 'Stop failed recharges. Get instant airtime with wallet billing.',
                    'subheadline' => 'Fast airtime purchases for individuals and businesses, with references and history you can reconcile.',
                    'primary_cta_label' => 'Create free account',
                    'secondary_cta_label' => 'Sign in',
                ],
                'pain_points' => [
                    [
                        'title' => 'USSD failures at the worst time',
                        'body' => 'When airtime is urgent, retries and network errors waste time and money.',
                    ],
                    [
                        'title' => 'No proof of purchase',
                        'body' => 'Manual transfers and screenshots don’t reconcile cleanly across teams.',
                    ],
                    [
                        'title' => 'Slow checkout every time',
                        'body' => 'Typing numbers repeatedly increases mistakes and delays.',
                    ],
                ],
                'benefits' => [
                    [
                        'title' => 'Instant fulfillment',
                        'body' => 'Complete top-ups quickly with a streamlined checkout flow.',
                        'icon' => 'fa-solid fa-bolt',
                    ],
                    [
                        'title' => 'Trackable references',
                        'body' => 'Every purchase generates a clear record you can search later.',
                        'icon' => 'fa-solid fa-receipt',
                    ],
                    [
                        'title' => 'Wallet-first billing',
                        'body' => 'Fund once and pay across services without switching apps.',
                        'icon' => 'fa-solid fa-wallet',
                    ],
                    [
                        'title' => 'Built for teams',
                        'body' => 'Keep history and references consistent for operations and support.',
                        'icon' => 'fa-solid fa-people-group',
                    ],
                ],
                'steps' => [
                    ['title' => 'Create an account', 'body' => 'Unlock VTU actions and save your history.'],
                    ['title' => 'Fund your wallet', 'body' => 'Use your preferred payment method to add funds.'],
                    ['title' => 'Top up instantly', 'body' => 'Choose network, amount, and confirm in seconds.'],
                ],
                'faq' => [
                    [
                        'q' => 'Can I see my past purchases?',
                        'a' => 'Yes. Your wallet and transaction history keep references you can use for reconciliation and support.',
                    ],
                    [
                        'q' => 'Do I need to log in to top up?',
                        'a' => 'Yes. VTU actions are only available after login to protect billing and prevent unauthorized use.',
                    ],
                ],
                'closing' => [
                    'headline' => 'Reconnect now—without the usual friction.',
                    'body' => 'Create an account to unlock airtime top-ups, wallet billing, and clean records for every purchase.',
                ],
            ],
            'image' => [
                'gradient' => 'linear-gradient(135deg, rgba(59,130,246,0.14), rgba(16,185,129,0.10))',
            ],
        ],
        [
            'slug' => 'data-bundles',
            'title' => 'VTU Data Bundles',
            'category' => 'finance',
            'icon' => 'fa-solid fa-wifi',
            'tagline' => 'Instant data bundles for work, streaming, and business.',
            'summary' => 'Buy data across networks quickly, keep transaction references, and stay online without interruptions.',
            'highlights' => [
                'Multi-network availability',
                'Fast, reliable fulfillment',
                'Trackable transaction history',
            ],
            'cta' => [
                'primary_label' => 'Create account',
                'secondary_label' => 'Sign in',
            ],
            'links' => [
                'primary' => '/register',
                'secondary' => '/login',
            ],
            'landing' => [
                'hero' => [
                    'headline_A' => 'Get data fast—avoid downtime and keep work moving.',
                    'headline_B' => 'Stay online. Buy data bundles instantly with wallet billing.',
                    'subheadline' => 'Reliable multi-network data purchases with references, history, and a checkout designed for speed.',
                    'primary_cta_label' => 'Create free account',
                    'secondary_cta_label' => 'Sign in',
                ],
                'pain_points' => [
                    [
                        'title' => 'Downtime kills momentum',
                        'body' => 'A few minutes offline can cost sales, deliveries, and customer trust.',
                    ],
                    [
                        'title' => 'Confusing bundle choices',
                        'body' => 'Choosing the right plan is harder when you’re in a hurry.',
                    ],
                    [
                        'title' => 'Hard-to-track spending',
                        'body' => 'Without clean references and history, accounting becomes guesswork.',
                    ],
                ],
                'benefits' => [
                    [
                        'title' => 'Multi-network coverage',
                        'body' => 'Purchase data across supported networks without switching tools.',
                        'icon' => 'fa-solid fa-signal',
                    ],
                    [
                        'title' => 'Fast checkout',
                        'body' => 'Built to complete purchases quickly when time matters.',
                        'icon' => 'fa-solid fa-gauge-high',
                    ],
                    [
                        'title' => 'References & history',
                        'body' => 'Searchable records help support, reconciliation, and budgeting.',
                        'icon' => 'fa-solid fa-clock-rotate-left',
                    ],
                    [
                        'title' => 'One wallet, many services',
                        'body' => 'Fund once and pay for the services you use most.',
                        'icon' => 'fa-solid fa-layer-group',
                    ],
                ],
                'steps' => [
                    ['title' => 'Create an account', 'body' => 'Unlock data bundle purchases and saved history.'],
                    ['title' => 'Fund your wallet', 'body' => 'Add funds using configured payment providers.'],
                    ['title' => 'Buy data', 'body' => 'Pick a network and bundle, confirm, and get back online.'],
                ],
                'faq' => [
                    [
                        'q' => 'Is this available without login?',
                        'a' => 'You can preview the flow publicly, but purchasing requires login to protect wallet billing.',
                    ],
                    [
                        'q' => 'Will I get a reference for each purchase?',
                        'a' => 'Yes. Transaction history records references that can be used for tracking and support.',
                    ],
                ],
                'closing' => [
                    'headline' => 'Stay online today—set up your wallet once.',
                    'body' => 'Create an account to unlock fast data purchases, references, and history you can rely on.',
                ],
            ],
            'image' => [
                'gradient' => 'linear-gradient(135deg, rgba(139,92,246,0.14), rgba(59,130,246,0.10))',
            ],
        ],
        [
            'slug' => 'cable-tv',
            'title' => 'Cable TV Subscription',
            'category' => 'finance',
            'icon' => 'fa-solid fa-tv',
            'tagline' => 'Renew your cable instantly—no missed shows.',
            'summary' => 'Subscribe DSTV, GOTV, and Startimes with wallet billing, trackable references, and a clean purchase history.',
            'highlights' => [
                'DSTV, GOTV, Startimes',
                'Fast renewals',
                'References and history',
            ],
            'cta' => [
                'primary_label' => 'Create account to subscribe',
                'secondary_label' => 'Sign in',
            ],
            'links' => [
                'primary' => '/register',
                'secondary' => '/login',
            ],
            'landing' => [
                'hero' => [
                    'headline_A' => 'Renew cable in minutes—DSTV, GOTV, Startimes.',
                    'headline_B' => 'Don’t miss the match. Subscribe cable instantly with wallet billing.',
                    'subheadline' => 'A smooth cable subscription flow with references for support and history for reconciliation.',
                    'primary_cta_label' => 'Create free account',
                    'secondary_cta_label' => 'Sign in',
                ],
                'pain_points' => [
                    [
                        'title' => 'Subscription downtime',
                        'body' => 'Late renewals mean missed content and frustrated households or customers.',
                    ],
                    [
                        'title' => 'Support headaches',
                        'body' => 'Without references, proving a renewal becomes slower than it should be.',
                    ],
                    [
                        'title' => 'Slow checkout',
                        'body' => 'When you’re renewing often, speed matters.',
                    ],
                ],
                'benefits' => [
                    ['title' => 'Fast renewals', 'body' => 'Get your subscription active quickly.', 'icon' => 'fa-solid fa-bolt'],
                    ['title' => 'Trackable references', 'body' => 'Keep proof-of-payment for support.', 'icon' => 'fa-solid fa-receipt'],
                    ['title' => 'Wallet-first billing', 'body' => 'Fund once and pay across VTU services.', 'icon' => 'fa-solid fa-wallet'],
                    ['title' => 'History for teams', 'body' => 'Reduce repeat issues and reconcile spend.', 'icon' => 'fa-solid fa-people-group'],
                ],
                'steps' => [
                    ['title' => 'Create an account', 'body' => 'Unlock cable subscriptions and save history.'],
                    ['title' => 'Fund your wallet', 'body' => 'Add funds with configured gateways.'],
                    ['title' => 'Subscribe', 'body' => 'Pick provider and package, confirm, and track.'],
                ],
                'faq' => [
                    [
                        'q' => 'Do I need to login to subscribe?',
                        'a' => 'Yes. Subscription payments require login to secure wallet billing and prevent abuse.',
                    ],
                ],
                'closing' => [
                    'headline' => 'Keep your screen on—renew now.',
                    'body' => 'Create an account to unlock cable subscriptions with trackable references and history.',
                ],
            ],
            'image' => [
                'gradient' => 'linear-gradient(135deg, rgba(139,92,246,0.16), rgba(59,130,246,0.10))',
            ],
        ],
        [
            'slug' => 'electricity-bills',
            'title' => 'Electricity Bills Payment',
            'category' => 'finance',
            'icon' => 'fa-solid fa-lightbulb',
            'tagline' => 'Pay your meter fast—keep references for proof.',
            'summary' => 'Pay electricity bills for DISCOs with wallet billing, references, and purchase history. Supports prepaid/postpaid flows.',
            'highlights' => [
                'Prepaid and postpaid',
                'DISCO support (e.g., IBEDC, EKEDC)',
                'References and history',
            ],
            'cta' => [
                'primary_label' => 'Create account to pay bills',
                'secondary_label' => 'Sign in',
            ],
            'links' => [
                'primary' => '/register',
                'secondary' => '/login',
            ],
            'landing' => [
                'hero' => [
                    'headline_A' => 'Pay electricity bills fast—prepaid or postpaid.',
                    'headline_B' => 'Avoid power downtime. Pay your meter with wallet billing.',
                    'subheadline' => 'A utility-payment flow designed for speed, traceability, and reconciliation with references and history.',
                    'primary_cta_label' => 'Create free account',
                    'secondary_cta_label' => 'Sign in',
                ],
                'pain_points' => [
                    [
                        'title' => 'Urgent meter recharges',
                        'body' => 'When the lights go out, you need a fast, reliable payment flow.',
                    ],
                    [
                        'title' => 'Hard to prove payment',
                        'body' => 'References and history make support faster and clearer.',
                    ],
                    [
                        'title' => 'Repeated bills',
                        'body' => 'A single wallet reduces friction when you pay often.',
                    ],
                ],
                'benefits' => [
                    ['title' => 'Fast checkout', 'body' => 'Pay quickly when time matters.', 'icon' => 'fa-solid fa-bolt'],
                    ['title' => 'Trackable references', 'body' => 'Keep a record for support and reconciliation.', 'icon' => 'fa-solid fa-receipt'],
                    ['title' => 'Wallet-first billing', 'body' => 'One balance across services.', 'icon' => 'fa-solid fa-wallet'],
                    ['title' => 'Clear workflow', 'body' => 'Prepaid/postpaid paths that reduce errors.', 'icon' => 'fa-solid fa-diagram-project'],
                ],
                'steps' => [
                    ['title' => 'Create an account', 'body' => 'Unlock bill payments and save history.'],
                    ['title' => 'Fund your wallet', 'body' => 'Add funds using configured gateways.'],
                    ['title' => 'Pay your meter', 'body' => 'Choose DISCO, enter meter number, and confirm.'],
                ],
                'faq' => [
                    [
                        'q' => 'Which DISCOs are supported?',
                        'a' => 'Your DISCO options depend on the provider integration configured by admin.',
                    ],
                ],
                'closing' => [
                    'headline' => 'Keep power steady—pay bills without friction.',
                    'body' => 'Create an account to unlock electricity payments with references and history.',
                ],
            ],
            'image' => [
                'gradient' => 'linear-gradient(135deg, rgba(16,185,129,0.14), rgba(59,130,246,0.10))',
            ],
        ],
        [
            'slug' => 'notary-services',
            'title' => 'Notary Services',
            'category' => 'legal',
            'icon' => 'fa-solid fa-scale-balanced',
            'tagline' => 'Submit notary requests and track progress.',
            'summary' => 'Connect with certified professionals for document signing and affidavits.',
            'highlights' => [
                'Request submission',
                'Status tracking',
                'Admin operations handling',
            ],
            'cta' => [
                'primary_label' => 'Create account',
                'secondary_label' => 'Sign in',
            ],
            'links' => [
                'primary' => '/register',
                'secondary' => '/login',
            ],
            'image' => [
                'gradient' => 'linear-gradient(135deg, rgba(245,158,11,0.12), rgba(59,130,246,0.10))',
            ],
        ],
        [
            'slug' => 'ticketing',
            'title' => 'Ticketing & Transport',
            'category' => 'commerce',
            'icon' => 'fa-solid fa-ticket',
            'tagline' => 'Sell and manage tickets and routes.',
            'summary' => 'Ticket purchase workflows with operational tracking and history.',
            'highlights' => [
                'Ticket purchase',
                'History and references',
                'Admin operations support',
            ],
            'cta' => [
                'primary_label' => 'Create account',
                'secondary_label' => 'Sign in',
            ],
            'links' => [
                'primary' => '/register',
                'secondary' => '/login',
            ],
            'image' => [
                'gradient' => 'linear-gradient(135deg, rgba(236,72,153,0.12), rgba(245,158,11,0.10))',
            ],
        ],
        [
            'slug' => 'post-office-logistics',
            'title' => 'Post Office & Logistics',
            'category' => 'logistics',
            'icon' => 'fa-solid fa-truck-fast',
            'tagline' => 'Ship, track, and manage logistics requests.',
            'summary' => 'Book shipments, track packages, and manage deliveries in a single workflow.',
            'highlights' => [
                'Shipment booking',
                'Tracking and references',
                'Admin operations visibility',
            ],
            'cta' => [
                'primary_label' => 'Create account',
                'secondary_label' => 'Sign in',
            ],
            'links' => [
                'primary' => '/register',
                'secondary' => '/login',
            ],
            'image' => [
                'gradient' => 'linear-gradient(135deg, rgba(59,130,246,0.14), rgba(245,158,11,0.10))',
            ],
        ],
        [
            'slug' => 'waec-pins',
            'title' => 'WAEC Result Checker',
            'category' => 'education',
            'icon' => 'fa-solid fa-graduation-cap',
            'tagline' => 'Buy WAEC result checker pins.',
            'summary' => 'Purchase pins and keep a record of transactions.',
            'highlights' => [
                'Pin purchase',
                'Wallet billing',
                'History tracking',
            ],
            'cta' => [
                'primary_label' => 'Create account',
                'secondary_label' => 'Sign in',
            ],
            'links' => [
                'primary' => '/register',
                'secondary' => '/login',
            ],
            'image' => [
                'gradient' => 'linear-gradient(135deg, rgba(16,185,129,0.12), rgba(59,130,246,0.10))',
            ],
        ],
        [
            'slug' => 'waec-registration-pins',
            'title' => 'WAEC Registration',
            'category' => 'education',
            'icon' => 'fa-solid fa-school',
            'tagline' => 'Buy WAEC registration pins.',
            'summary' => 'Purchase registration pins with reliable fulfillment and history.',
            'highlights' => [
                'Registration pins',
                'Wallet billing',
                'History tracking',
            ],
            'cta' => [
                'primary_label' => 'Create account',
                'secondary_label' => 'Sign in',
            ],
            'links' => [
                'primary' => '/register',
                'secondary' => '/login',
            ],
            'image' => [
                'gradient' => 'linear-gradient(135deg, rgba(139,92,246,0.12), rgba(16,185,129,0.10))',
            ],
        ],
        [
            'slug' => 'motor-insurance',
            'title' => 'Motor Insurance',
            'category' => 'insurance',
            'icon' => 'fa-solid fa-car-burst',
            'tagline' => 'Buy and manage motor insurance.',
            'summary' => 'Insurance purchase flows with options lookup and fulfillment tracking.',
            'highlights' => [
                'Options lookup',
                'Purchase workflow',
                'History and references',
            ],
            'cta' => [
                'primary_label' => 'Create account',
                'secondary_label' => 'Sign in',
            ],
            'links' => [
                'primary' => '/register',
                'secondary' => '/login',
            ],
            'image' => [
                'gradient' => 'linear-gradient(135deg, rgba(236,72,153,0.12), rgba(59,130,246,0.10))',
            ],
        ],
    ],
];
