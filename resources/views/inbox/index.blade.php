@extends('layouts.app')

@section('title', 'Inbox - Dokumen Menunggu Persetujuan')

@push('styles')
    <style>
        /* ============================================
                                                   MODERN INBOX DESIGN - CLEAN & PROFESSIONAL
                                                   ============================================ */

        :root {
            --primary-color: #083E40;
            --primary-light: #0a4f52;
            --secondary-color: #889717;
            --accent-blue: #4299e1;
            --accent-green: #48bb78;
            --accent-orange: #f6ad55;
            --accent-red: #f56565;
            --bg-light: #f7fafc;
            --bg-white: #ffffff;
            --text-primary: #1a202c;
            --text-secondary: #4a5568;
            --text-muted: #718096;
            --border-color: #e2e8f0;
            --shadow-sm: 0 1px 2px rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px rgba(0, 0, 0, 0.07);
            --shadow-lg: 0 10px 15px rgba(0, 0, 0, 0.1);
            --shadow-xl: 0 20px 25px rgba(0, 0, 0, 0.1);
        }

        /* Base Container */
        .inbox-container {
            background: var(--bg-light);
            min-height: calc(100vh - 80px);
            padding: 2rem 1.5rem;
        }

        /* ============================================
                                                   HEADER SECTION - Clean & Professional
                                                   ============================================ */
        .inbox-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-light) 100%);
            border-radius: 16px;
            padding: 2rem 2.5rem;
            margin-bottom: 1.5rem;
            box-shadow: var(--shadow-lg);
            position: relative;
            overflow: hidden;
        }

        .inbox-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 300px;
            height: 300px;
            background: rgba(255, 255, 255, 0.03);
            border-radius: 50%;
        }

        .inbox-header-content {
            position: relative;
            z-index: 10;
        }

        .greeting-section {
            color: white;
        }

        .greeting-time {
            font-size: 0.8125rem;
            font-weight: 500;
            opacity: 0.85;
            margin-bottom: 0.5rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .greeting-name {
            font-size: 1.875rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .greeting-name .wave {
            font-size: 1.875rem;
            animation: wave 2s ease-in-out infinite;
        }

        @keyframes wave {

            0%,
            100% {
                transform: rotate(0deg);
            }

            10%,
            30% {
                transform: rotate(14deg);
            }

            20% {
                transform: rotate(-8deg);
            }

            40%,
            60% {
                transform: rotate(0deg);
            }

            50% {
                transform: rotate(-10deg);
            }
        }

        .greeting-subtitle {
            font-size: 0.9375rem;
            opacity: 0.9;
            line-height: 1.6;
            margin: 0;
        }

        .breadcrumb-custom {
            background: rgba(255, 255, 255, 0.12);
            backdrop-filter: blur(10px);
            border-radius: 10px;
            padding: 0.625rem 1rem;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .breadcrumb-custom a {
            color: white;
            text-decoration: none;
            opacity: 0.85;
            transition: opacity 0.2s;
            font-size: 0.875rem;
        }

        .breadcrumb-custom a:hover {
            opacity: 1;
        }

        .breadcrumb-custom .separator {
            opacity: 0.6;
            margin: 0 0.375rem;
        }

        .breadcrumb-custom .current {
            color: white;
            font-weight: 600;
            opacity: 1;
            font-size: 0.875rem;
        }

        /* ============================================
                                                   STATS CARDS - Clean & Organized
                                                   ============================================ */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 1.25rem;
            margin-bottom: 1.5rem;
        }

        .stat-card {
            background: var(--bg-white);
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--border-color);
            transition: all 0.2s ease;
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: var(--accent-color);
            transform: scaleX(0);
            transform-origin: left;
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
            border-color: var(--accent-color);
        }

        .stat-card:hover::before {
            transform: scaleX(1);
        }

        .stat-card.waiting {
            --accent-color: var(--accent-orange);
        }

        .stat-card.approved {
            --accent-color: var(--accent-green);
        }

        .stat-card.total {
            --accent-color: var(--accent-blue);
        }

        .stat-card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .stat-icon-wrapper {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.375rem;
            color: white;
            flex-shrink: 0;
        }

        .stat-card.waiting .stat-icon-wrapper {
            background: linear-gradient(135deg, #f6ad55 0%, #fed7aa 100%);
        }

        .stat-card.approved .stat-icon-wrapper {
            background: linear-gradient(135deg, #48bb78 0%, #9ae6b4 100%);
        }

        .stat-card.total .stat-icon-wrapper {
            background: linear-gradient(135deg, #4299e1 0%, #90cdf4 100%);
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: var(--text-primary);
            line-height: 1.2;
            margin-bottom: 0.375rem;
        }

        .stat-label {
            font-size: 0.8125rem;
            font-weight: 600;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* ============================================
                                                   DOCUMENT SECTION - Clean Card Design
                                                   ============================================ */
        .documents-section {
            background: var(--bg-white);
            border-radius: 16px;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--border-color);
            overflow: hidden;
        }

        .documents-header {
            padding: 1.75rem 2rem;
            border-bottom: 1px solid var(--border-color);
            background: var(--bg-white);
        }

        .documents-title {
            font-size: 1.375rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 0.375rem;
            display: flex;
            align-items: center;
            gap: 0.625rem;
        }

        .documents-title i {
            color: var(--accent-blue);
            font-size: 1.25rem;
        }

        .documents-subtitle {
            color: var(--text-muted);
            font-size: 0.875rem;
            margin: 0;
        }

        .documents-count {
            background: linear-gradient(135deg, var(--accent-blue) 0%, #63b3ed 100%);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 10px;
            font-weight: 600;
            font-size: 0.8125rem;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            box-shadow: 0 2px 8px rgba(66, 153, 225, 0.25);
        }

        /* ============================================
                                                   SEARCH & FILTER - Clean Input Design
                                                   ============================================ */
        .search-filter-section {
            padding: 1.5rem 2rem;
            background: var(--bg-light);
            border-bottom: 1px solid var(--border-color);
        }

        .search-filter-wrapper {
            display: flex;
            gap: 1rem;
            align-items: center;
            flex-wrap: wrap;
        }

        .search-input-wrapper {
            flex: 1;
            min-width: 280px;
            position: relative;
        }

        .search-input {
            width: 100%;
            padding: 0.75rem 1rem 0.75rem 2.75rem;
            border: 1.5px solid var(--border-color);
            border-radius: 10px;
            font-size: 0.875rem;
            transition: all 0.2s ease;
            background: var(--bg-white);
            color: var(--text-primary);
        }

        .search-input:focus {
            outline: none;
            border-color: var(--accent-blue);
            box-shadow: 0 0 0 3px rgba(66, 153, 225, 0.1);
        }

        .search-input::placeholder {
            color: var(--text-muted);
        }

        .search-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            font-size: 0.875rem;
        }

        .filter-select {
            padding: 0.75rem 1rem;
            border: 1.5px solid var(--border-color);
            border-radius: 10px;
            font-size: 0.875rem;
            background: var(--bg-white);
            color: var(--text-primary);
            cursor: pointer;
            transition: all 0.2s ease;
            min-width: 160px;
        }

        .filter-select:focus {
            outline: none;
            border-color: var(--accent-blue);
            box-shadow: 0 0 0 3px rgba(66, 153, 225, 0.1);
        }

        /* ============================================
                                                   DOCUMENT CARDS - Clean Card Layout
                                                   ============================================ */
        .documents-list {
            padding: 1.5rem 2rem;
        }

        .document-card {
            background: var(--bg-white);
            border: 1.5px solid var(--border-color);
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            transition: all 0.2s ease;
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }

        .document-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 3px;
            height: 100%;
            background: var(--accent-orange);
            transform: scaleY(0);
            transform-origin: top;
            transition: transform 0.2s ease;
        }

        .document-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
            border-color: var(--accent-blue);
        }

        .document-card:hover::before {
            transform: scaleY(1);
        }

        .document-card:last-child {
            margin-bottom: 0;
        }

        .document-card-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1rem;
            gap: 1rem;
        }

        .document-number {
            flex: 1;
            min-width: 0;
        }

        .document-agenda {
            font-size: 1rem;
            font-weight: 700;
            color: var(--accent-blue);
            margin-bottom: 0.25rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .document-agenda i {
            font-size: 0.875rem;
            flex-shrink: 0;
        }

        .document-spp {
            font-size: 0.8125rem;
            color: var(--text-secondary);
            font-weight: 500;
        }

        .document-status-badge {
            background: linear-gradient(135deg, var(--accent-orange) 0%, #fed7aa 100%);
            color: white;
            padding: 0.4375rem 0.875rem;
            border-radius: 20px;
            font-size: 0.6875rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            white-space: nowrap;
            box-shadow: 0 2px 6px rgba(246, 173, 85, 0.25);
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
        }

        .document-status-badge i {
            font-size: 0.625rem;
        }

        .document-body {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 1.25rem;
            margin-bottom: 1rem;
        }

        .document-info-item {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .document-info-label {
            font-size: 0.6875rem;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .document-info-value {
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--text-primary);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .document-info-value i {
            color: var(--accent-blue);
            font-size: 0.8125rem;
            flex-shrink: 0;
        }

        .document-info-value.text-success {
            color: var(--accent-green);
            font-size: 0.9375rem;
        }

        .document-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 1rem;
            border-top: 1px solid var(--border-color);
            gap: 1rem;
            flex-wrap: wrap;
        }

        .document-time {
            font-size: 0.8125rem;
            color: var(--text-muted);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .document-time i {
            font-size: 0.75rem;
        }

        .btn-view-detail {
            background: linear-gradient(135deg, var(--accent-blue) 0%, #63b3ed 100%);
            color: white;
            padding: 0.625rem 1.25rem;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.8125rem;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.2s ease;
            border: none;
            cursor: pointer;
            box-shadow: 0 2px 8px rgba(66, 153, 225, 0.25);
        }

        .btn-view-detail:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(66, 153, 225, 0.35);
            color: white;
            text-decoration: none;
        }

        .btn-view-detail i {
            font-size: 0.75rem;
        }

        /* ============================================
                                                   EMPTY STATE - Clean & Encouraging
                                                   ============================================ */
        .empty-state {
            padding: 3.5rem 2rem;
            text-align: center;
        }

        .empty-state-icon {
            width: 140px;
            height: 140px;
            margin: 0 auto 1.5rem;
            background: linear-gradient(135deg, #f0f4f8 0%, #e2e8f0 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3.5rem;
            animation: float 3s ease-in-out infinite;
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0px);
            }

            50% {
                transform: translateY(-8px);
            }
        }

        .empty-state-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
        }

        .empty-state-subtitle {
            font-size: 0.9375rem;
            color: var(--text-muted);
            line-height: 1.6;
            max-width: 480px;
            margin: 0 auto 1.5rem;
        }

        .btn-refresh {
            background: linear-gradient(135deg, var(--accent-blue) 0%, #63b3ed 100%);
            color: white;
            padding: 0.75rem 1.75rem;
            border-radius: 10px;
            font-weight: 600;
            font-size: 0.875rem;
            border: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.2s ease;
            box-shadow: 0 2px 8px rgba(66, 153, 225, 0.25);
        }

        .btn-refresh:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(66, 153, 225, 0.35);
        }

        /* ============================================
                                                   PAGINATION - Clean Design
                                                   ============================================ */
        .pagination-wrapper {
            padding: 1.5rem 2rem;
            border-top: 1px solid var(--border-color);
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 1rem;
            background: var(--bg-light);
        }

        /* Laravel Default Pagination Container */
        .pagination-wrapper>nav {
            width: 100%;
        }

        /* Main flex container for "Previous" and "Next" with page numbers */
        .pagination-wrapper nav>div {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 1rem;
        }

        /* Hide the first div that shows "Showing X to Y of Z results" on mobile, show on desktop */
        .pagination-wrapper nav>div:first-child {
            display: none;
        }

        @media (min-width: 640px) {
            .pagination-wrapper nav>div:first-child {
                display: block;
            }
        }

        /* The second div contains the actual pagination links */
        .pagination-wrapper nav>div:last-child {
            display: flex;
            justify-content: center;
            align-items: center;
            width: 100%;
        }

        /* Results info text */
        .pagination-wrapper p {
            font-size: 0.875rem;
            color: var(--text-secondary);
            margin: 0;
            text-align: center;
        }

        .pagination-wrapper p span {
            font-weight: 600;
            color: var(--text-primary);
        }

        /* Previous/Next links and page number container */
        .pagination-wrapper nav span,
        .pagination-wrapper nav a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 40px;
            height: 40px;
            padding: 0.5rem 0.875rem;
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--text-primary);
            background: var(--bg-white);
            border: 1.5px solid var(--border-color);
            text-decoration: none;
            transition: all 0.2s ease;
            cursor: pointer;
        }

        /* First and last items get rounded corners */
        .pagination-wrapper nav span:first-child,
        .pagination-wrapper nav a:first-child {
            border-top-left-radius: 8px;
            border-bottom-left-radius: 8px;
        }

        .pagination-wrapper nav span:last-child,
        .pagination-wrapper nav a:last-child {
            border-top-right-radius: 8px;
            border-bottom-right-radius: 8px;
        }

        /* Remove double borders */
        .pagination-wrapper nav span+span,
        .pagination-wrapper nav span+a,
        .pagination-wrapper nav a+span,
        .pagination-wrapper nav a+a {
            margin-left: -1.5px;
        }

        /* Hover state for links */
        .pagination-wrapper nav a:hover {
            background: var(--accent-blue);
            color: white;
            border-color: var(--accent-blue);
            z-index: 1;
            position: relative;
        }

        /* Active page - using aria-current */
        .pagination-wrapper nav span[aria-current="page"] {
            background: var(--accent-blue);
            color: white;
            border-color: var(--accent-blue);
            z-index: 1;
            position: relative;
        }

        /* Disabled state (usually for "Previous" on first page, "Next" on last page) */
        .pagination-wrapper nav span[aria-disabled="true"],
        .pagination-wrapper nav span.cursor-not-allowed {
            color: var(--text-muted);
            background: var(--bg-light);
            cursor: not-allowed;
            opacity: 0.6;
        }

        /* FIX: SVG icons in Laravel pagination - THIS IS CRITICAL */
        .pagination-wrapper svg {
            width: 16px !important;
            height: 16px !important;
            max-width: 16px !important;
            max-height: 16px !important;
            flex-shrink: 0;
        }

        /* Alternative pagination layout for Tailwind pagination */
        .pagination-wrapper .relative.inline-flex,
        .pagination-wrapper .inline-flex {
            display: inline-flex;
            align-items: center;
        }

        /* Fix for the flex layout that wraps pagination */
        .pagination-wrapper .flex.justify-between,
        .pagination-wrapper .sm\\:flex-1,
        .pagination-wrapper .sm\\:items-center,
        .pagination-wrapper .sm\\:justify-between {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 1rem;
            width: 100%;
        }

        @media (min-width: 640px) {

            .pagination-wrapper .flex.justify-between,
            .pagination-wrapper .sm\\:flex-1.sm\\:flex.sm\\:items-center.sm\\:justify-between {
                flex-direction: row;
                justify-content: space-between;
            }
        }

        /* Hide the mobile simple pagination, show full pagination */
        .pagination-wrapper .flex-1.flex.justify-between.sm\\:hidden {
            display: none;
        }

        .pagination-wrapper .hidden.sm\\:flex-1 {
            display: flex !important;
            flex-direction: column;
            align-items: center;
            gap: 1rem;
        }

        @media (min-width: 640px) {
            .pagination-wrapper .hidden.sm\\:flex-1 {
                flex-direction: row;
                justify-content: space-between;
            }
        }

        /* Standard Bootstrap/Laravel Pagination */
        .pagination-wrapper .pagination {
            margin: 0;
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 0.25rem;
        }

        .pagination-wrapper .page-item {
            margin: 0;
        }

        .pagination-wrapper .page-link {
            border-radius: 8px;
            border: 1.5px solid var(--border-color);
            color: var(--text-primary);
            padding: 0.5rem 0.875rem;
            transition: all 0.2s ease;
            font-size: 0.875rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 40px;
            height: 40px;
        }

        .pagination-wrapper .page-link:hover {
            background: var(--accent-blue);
            color: white;
            border-color: var(--accent-blue);
        }

        .pagination-wrapper .page-item.active .page-link {
            background: var(--accent-blue);
            border-color: var(--accent-blue);
            color: white;
        }

        .pagination-wrapper .page-item.disabled .page-link {
            color: var(--text-muted);
            background: var(--bg-light);
            cursor: not-allowed;
            opacity: 0.6;
        }

        /* SVG icons in page links */
        .pagination-wrapper .page-link svg {
            width: 14px !important;
            height: 14px !important;
        }

        /* ============================================
                                                   RESPONSIVE DESIGN
                                                   ============================================ */
        @media (max-width: 768px) {
            .inbox-container {
                padding: 1rem;
            }

            .inbox-header {
                padding: 1.5rem;
                border-radius: 12px;
            }

            .greeting-name {
                font-size: 1.5rem;
            }

            .stats-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }

            .search-filter-wrapper {
                flex-direction: column;
            }

            .search-input-wrapper {
                min-width: 100%;
            }

            .filter-select {
                width: 100%;
            }

            .documents-header {
                padding: 1.25rem 1.5rem;
            }

            .documents-list {
                padding: 1.25rem 1.5rem;
            }

            .document-body {
                grid-template-columns: 1fr;
                gap: 1rem;
            }

            .document-footer {
                flex-direction: column;
                align-items: stretch;
            }

            .btn-view-detail {
                width: 100%;
                justify-content: center;
            }

            .search-filter-section {
                padding: 1.25rem 1.5rem;
            }
        }

        /* ============================================
                                                   UTILITY CLASSES
                                                   ============================================ */
        .text-truncate-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        /* ============================================
                                                   NOTIFICATION BADGE
                                                   ============================================ */
        .new-documents-badge {
            background: linear-gradient(135deg, #f56565 0%, #fc8181 100%);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 10px;
            font-weight: 600;
            font-size: 0.8125rem;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            box-shadow: 0 2px 8px rgba(245, 101, 101, 0.3);
            animation: pulse 2s ease-in-out infinite;
        }

        .new-documents-badge i {
            font-size: 0.875rem;
        }

        @keyframes pulse {

            0%,
            100% {
                transform: scale(1);
                box-shadow: 0 2px 8px rgba(245, 101, 101, 0.3);
            }

            50% {
                transform: scale(1.05);
                box-shadow: 0 4px 12px rgba(245, 101, 101, 0.5);
            }
        }

        .new-badge {
            background: linear-gradient(135deg, #48bb78 0%, #9ae6b4 100%);
            color: white;
            padding: 0.375rem 0.75rem;
            border-radius: 20px;
            font-size: 0.625rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            white-space: nowrap;
            box-shadow: 0 2px 6px rgba(72, 187, 120, 0.3);
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            animation: newBadgePulse 2s ease-in-out infinite;
        }

        .new-badge i {
            font-size: 0.5rem;
        }

        @keyframes newBadgePulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.8;
            }
        }

        /* History Card - Clickable Style */
        .stat-card.history {
            --accent-color: #083E40;
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }

        .stat-card.history::after {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, transparent 50%, rgba(8, 62, 64, 0.1) 50%);
            border-radius: 0 12px 0 0;
        }

        .stat-card.history .stat-icon-wrapper {
            background: linear-gradient(135deg, #083E40 0%, #0a5a5c 100%);
        }

        .stat-card.history:hover {
            transform: translateY(-4px) scale(1.02);
            box-shadow: 0 8px 25px rgba(8, 62, 64, 0.25);
            border-color: #083E40;
        }

        .stat-card.history:hover::before {
            transform: scaleX(1);
        }

        .stat-card.history:active {
            transform: translateY(-2px) scale(1.01);
        }

        .stat-hint {
            font-size: 0.6875rem;
            color: #083E40;
            margin-top: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.375rem;
            opacity: 0.8;
            transition: opacity 0.2s ease;
        }

        .stat-card.history:hover .stat-hint {
            opacity: 1;
        }

        .stat-hint i {
            font-size: 0.625rem;
            animation: pointBounce 1.5s ease-in-out infinite;
        }

        @keyframes pointBounce {

            0%,
            100% {
                transform: translateX(0);
            }

            50% {
                transform: translateX(3px);
            }
        }

        /* History Modal Styles */
        .history-modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(4px);
            z-index: 9999;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }

        .history-modal-overlay.active {
            display: flex;
            animation: fadeIn 0.2s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        .history-modal {
            background: white;
            border-radius: 16px;
            width: 100%;
            max-width: 700px;
            max-height: 85vh;
            overflow: hidden;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.25);
            animation: slideUp 0.3s ease;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(20px) scale(0.98);
            }

            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .history-modal-header {
            padding: 1.5rem 1.75rem;
            background: linear-gradient(135deg, #083E40 0%, #0a5a5c 100%);
            color: white;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .history-modal-title {
            font-size: 1.25rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin: 0;
        }

        .history-modal-title i {
            font-size: 1.125rem;
        }

        .history-modal-close {
            background: rgba(255, 255, 255, 0.2);
            border: none;
            color: white;
            width: 36px;
            height: 36px;
            border-radius: 10px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
        }

        .history-modal-close:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: scale(1.05);
        }

        .history-modal-filters {
            padding: 1.25rem 1.75rem;
            background: #f8f9fa;
            border-bottom: 1px solid var(--border-color);
        }

        .filter-buttons {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
            margin-bottom: 1rem;
        }

        .filter-btn {
            padding: 0.5rem 1rem;
            border: 1.5px solid var(--border-color);
            background: white;
            border-radius: 8px;
            font-size: 0.8125rem;
            font-weight: 500;
            color: var(--text-secondary);
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .filter-btn:hover {
            border-color: #083E40;
            color: #083E40;
        }

        .filter-btn.active {
            background: #083E40;
            border-color: #083E40;
            color: white;
        }

        .custom-date-wrapper {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            flex-wrap: wrap;
        }

        .custom-date-wrapper label {
            font-size: 0.8125rem;
            color: var(--text-secondary);
            font-weight: 500;
        }

        .history-modal-filters .custom-date-wrapper input[type="date"],
        .custom-date-wrapper input[type="date"],
        input#historyCustomDate {
            padding: 0.625rem 1rem !important;
            padding-left: 2.5rem !important;
            border: 2px solid #e2e8f0 !important;
            border-radius: 10px !important;
            font-size: 0.875rem !important;
            font-weight: 500 !important;
            color: #1a202c !important;
            cursor: pointer;
            background: white !important;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='20' height='20' viewBox='0 0 24 24' fill='none' stroke='%23083E40' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Crect x='3' y='4' width='18' height='18' rx='2' ry='2'/%3E%3Cline x1='16' y1='2' x2='16' y2='6'/%3E%3Cline x1='8' y1='2' x2='8' y2='6'/%3E%3Cline x1='3' y1='10' x2='21' y2='10'/%3E%3C/svg%3E") !important;
            background-repeat: no-repeat !important;
            background-position: 0.75rem center !important;
            background-size: 18px !important;
            transition: all 0.2s ease;
            min-width: 200px;
            height: auto !important;
            -webkit-appearance: none !important;
            -moz-appearance: none !important;
            appearance: none !important;
        }

        .history-modal-filters .custom-date-wrapper input[type="date"]:hover,
        .custom-date-wrapper input[type="date"]:hover,
        input#historyCustomDate:hover {
            border-color: #083E40 !important;
            background-color: #f8fafa !important;
        }

        .history-modal-filters .custom-date-wrapper input[type="date"]:focus,
        .custom-date-wrapper input[type="date"]:focus,
        input#historyCustomDate:focus {
            outline: none !important;
            border-color: #083E40 !important;
            box-shadow: 0 0 0 4px rgba(8, 62, 64, 0.12) !important;
            background-color: white !important;
        }

        input[type="date"]::-webkit-calendar-picker-indicator {
            cursor: pointer;
            opacity: 0.6;
            transition: opacity 0.2s ease;
            padding: 0.25rem;
        }

        input[type="date"]::-webkit-calendar-picker-indicator:hover {
            opacity: 1;
        }

        .history-modal-body {
            padding: 1.25rem 1.75rem;
            max-height: 400px;
            overflow-y: auto;
        }

        .history-date-label {
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .history-date-label .count-badge {
            background: #083E40;
            color: white;
            padding: 0.25rem 0.625rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .history-doc-list {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .history-doc-item {
            background: #f8f9fa;
            border: 1.5px solid var(--border-color);
            border-radius: 10px;
            padding: 1rem 1.25rem;
            transition: all 0.2s ease;
            cursor: pointer;
        }

        .history-doc-item:hover {
            border-color: #083E40;
            box-shadow: 0 2px 8px rgba(8, 62, 64, 0.1);
            transform: translateX(4px);
        }

        .history-doc-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 0.5rem;
        }

        .history-doc-agenda {
            font-size: 0.9375rem;
            font-weight: 700;
            color: #083E40;
        }

        .history-doc-status {
            padding: 0.25rem 0.625rem;
            border-radius: 20px;
            font-size: 0.6875rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .history-doc-status.pending {
            background: #fed7aa;
            color: #c05621;
        }

        .history-doc-status.approved {
            background: #c6f6d5;
            color: #276749;
        }

        .history-doc-status.rejected {
            background: #fed7d7;
            color: #c53030;
        }

        .history-doc-spp {
            font-size: 0.8125rem;
            color: var(--text-secondary);
            margin-bottom: 0.375rem;
        }

        .history-doc-uraian {
            font-size: 0.8125rem;
            color: var(--text-muted);
            margin-bottom: 0.5rem;
        }

        .history-doc-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 0.5rem;
            border-top: 1px solid var(--border-color);
        }

        .history-doc-time {
            font-size: 0.75rem;
            color: var(--text-muted);
            display: flex;
            align-items: center;
            gap: 0.375rem;
        }

        .history-doc-value {
            font-size: 0.8125rem;
            font-weight: 700;
            color: #1a202c;
        }

        .history-empty-state {
            text-align: center;
            padding: 2rem 1rem;
            color: var(--text-muted);
        }

        .history-empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }

        .history-empty-state p {
            font-size: 0.9375rem;
            margin: 0;
        }

        .history-loading {
            text-align: center;
            padding: 2rem 1rem;
            color: var(--text-muted);
        }

        .history-loading i {
            font-size: 2rem;
            animation: spin 1s linear infinite;
            margin-bottom: 0.75rem;
        }

        @keyframes spin {
            from {
                transform: rotate(0deg);
            }

            to {
                transform: rotate(360deg);
            }
        }

        /* ============================================
               DARK MODE OVERRIDES
               ============================================ */

        /* Container backgrounds */
        .dark .inbox-container {
            background: #0f172a !important;
        }

        .dark .stats-grid .stat-card {
            background: #1e293b !important;
            border-color: #334155 !important;
        }

        /* Stats card values - white */
        .dark .stat-value {
            color: #ffffff !important;
        }

        /* Stats card labels - lighter */
        .dark .stat-label {
            color: #94a3b8 !important;
        }

        /* Documents section */
        .dark .documents-section {
            background: #1e293b !important;
            border-color: #334155 !important;
        }

        .dark .documents-header {
            background: #1e293b !important;
            border-color: #334155 !important;
        }

        .dark .documents-title {
            color: #ffffff !important;
        }

        .dark .documents-title i {
            color: #4ade80 !important;
        }

        .dark .documents-subtitle {
            color: #94a3b8 !important;
        }

        /* Search filter section */
        .dark .search-filter-section {
            background: #0f172a !important;
            border-color: #334155 !important;
        }

        .dark .search-input {
            background: #1e293b !important;
            border-color: #475569 !important;
            color: #ffffff !important;
        }

        .dark .search-input::placeholder {
            color: #64748b !important;
        }

        .dark .filter-select {
            background: #1e293b !important;
            border-color: #475569 !important;
            color: #ffffff !important;
        }

        /* Documents list */
        .dark .documents-list {
            background: #1e293b !important;
        }

        /* Document cards */
        .dark .document-card {
            background: #0f172a !important;
            border-color: #334155 !important;
        }

        .dark .document-card:hover {
            border-color: #4ade80 !important;
        }

        .dark .document-agenda {
            color: #4ade80 !important;
        }

        .dark .document-spp {
            color: #94a3b8 !important;
        }

        .dark .document-info-label {
            color: #64748b !important;
        }

        .dark .document-info-value {
            color: #ffffff !important;
        }

        .dark .document-info-value i {
            color: #4ade80 !important;
        }

        .dark .document-footer {
            border-color: #334155 !important;
        }

        .dark .document-time {
            color: #94a3b8 !important;
        }

        /* Empty state */
        .dark .empty-state-icon {
            background: linear-gradient(135deg, #1e293b 0%, #334155 100%) !important;
        }

        .dark .empty-state-title {
            color: #ffffff !important;
        }

        .dark .empty-state-subtitle {
            color: #94a3b8 !important;
        }

        /* Pagination */
        .dark .pagination-wrapper {
            background: #1e293b !important;
            border-color: #334155 !important;
        }

        .dark .pagination-wrapper nav span,
        .dark .pagination-wrapper nav a {
            background: #0f172a !important;
            border-color: #334155 !important;
            color: #ffffff !important;
        }

        .dark .pagination-wrapper p {
            color: #94a3b8 !important;
        }

        .dark .pagination-wrapper p span {
            color: #ffffff !important;
        }

        /* History modal */
        .dark .history-modal {
            background: #1e293b !important;
        }

        .dark .history-modal-header {
            background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%) !important;
        }

        .dark .history-modal-body {
            background: #1e293b !important;
        }

        .dark .history-doc-card {
            background: #0f172a !important;
            border-color: #334155 !important;
        }

        .dark .history-doc-agenda {
            color: #4ade80 !important;
        }

        .dark .history-doc-spp,
        .dark .history-doc-uraian {
            color: #94a3b8 !important;
        }

        .dark .history-doc-footer {
            border-color: #334155 !important;
        }

        .dark .history-empty-state {
            color: #94a3b8 !important;
        }
    </style>

@endpush

@section('content')
    <div class="inbox-container">
        <div class="container-fluid px-0">
            <!-- Header Section -->
            <div class="inbox-header">
                <div class="inbox-header-content">
                    <div class="row align-items-center">
                        <div class="col-lg-8">
                            <div class="greeting-section">
                                <div class="greeting-time">
                                    @php
                                        $hour = date('H');
                                        $greeting = $hour < 12 ? 'Selamat Pagi' : ($hour < 15 ? 'Selamat Siang' : ($hour < 18 ? 'Selamat Sore' : 'Selamat Malam'));
                                        $userName = $userRole === 'team_verifikasi' ? 'Team Verifikasi' : ucfirst($userRole);
                                    @endphp
                                    {{ $greeting }}
                                </div>
                                <h1 class="greeting-name">
                                    <span>{{ $userName }}</span>
                                    <span class="wave">👋</span>
                                </h1>
                                <p class="greeting-subtitle">
                                    Kelola dan persetujui dokumen yang masuk ke inbox Anda dengan mudah dan efisien.
                                </p>
                            </div>
                        </div>
                        <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                            <div class="breadcrumb-custom">
                                @php
                                    $dashboardUrl = match ($userRole) {
                                        'team_verifikasi' => '/dashboardB',
                                        'Perpajakan' => '/dashboardPerpajakan',
                                        'Akutansi' => '/dashboardAkutansi',
                                        default => '/dashboard'
                                    };
                                @endphp
                                <a href="{{ url($dashboardUrl) }}">Home</a>
                                <span class="separator">/</span>
                                <span class="current">Inbox</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="stats-grid">
                <div class="stat-card waiting">
                    <div class="stat-card-header">
                        <div>
                            <div class="stat-value">{{ $documents->total() }}</div>
                            <div class="stat-label">Menunggu Persetujuan</div>
                        </div>
                        <div class="stat-icon-wrapper">
                            <i class="fas fa-clock"></i>
                        </div>
                    </div>
                </div>

                <div class="stat-card approved">
                    <div class="stat-card-header">
                        <div>
                            <div class="stat-value">{{ $approvedToday ?? 0 }}</div>
                            <div class="stat-label">Disetujui Hari Ini</div>
                        </div>
                        <div class="stat-icon-wrapper">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                </div>

                <div class="stat-card history clickable" onclick="openHistoryModal()"
                    title="Klik untuk melihat riwayat dokumen masuk">
                    <div class="stat-card-header">
                        <div>
                            <div class="stat-value">{{ $totalProcessed ?? 0 }}</div>
                            <div class="stat-label">Riwayat Inbox</div>
                            <div class="stat-hint">
                                <i class="fas fa-hand-pointer"></i> Klik untuk lihat riwayat
                            </div>
                        </div>
                        <div class="stat-icon-wrapper">
                            <i class="fas fa-history"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Documents Section -->
            <div class="documents-section">
                <div class="documents-header">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h2 class="documents-title">
                                <i class="fas fa-inbox"></i>
                                Dokumen Menunggu Persetujuan
                            </h2>
                            <p class="documents-subtitle">Tinjau dan berikan persetujuan untuk dokumen yang masuk</p>
                        </div>
                        <div class="col-md-4 text-md-end mt-3 mt-md-0">
                            <div class="d-flex align-items-center justify-content-md-end gap-2 flex-wrap">
                                @if($newDocumentsCount > 0)
                                    <span class="new-documents-badge" id="newDocumentsBadge">
                                        <i class="fas fa-bell"></i>
                                        <span id="newDocumentsCount">{{ $newDocumentsCount }}</span> Baru
                                    </span>
                                @endif
                                <span class="documents-count">
                                    <i class="fas fa-file-alt"></i>
                                    {{ $documents->total() }} Dokumen
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                @if($documents->count() > 0)
                    <!-- Search & Filter -->
                    <div class="search-filter-section">
                        <div class="search-filter-wrapper">
                            <div class="search-input-wrapper">
                                <i class="fas fa-search search-icon"></i>
                                <input type="text" class="search-input" id="searchInput"
                                    placeholder="Cari berdasarkan nomor agenda, SPP, atau uraian...">
                            </div>
                            <select class="filter-select" id="filterSelect">
                                <option value="">Semua Status</option>
                                <option value="pending">Menunggu Persetujuan</option>
                                <option value="approved">Disetujui</option>
                                <option value="rejected">Ditolak</option>
                            </select>
                        </div>
                    </div>

                    <!-- Select All Header for Bulk Actions -->
                    <div class="select-all-container" id="selectAllContainer">
                        <label class="bulk-checkbox">
                            <input type="checkbox" id="selectAllCheckbox" onchange="toggleSelectAll()">
                            <span class="checkmark"></span>
                        </label>
                        <label class="select-all-label" for="selectAllCheckbox">Pilih Semua</label>
                        <span class="selected-count-badge" id="selectedCountBadge">0 dipilih</span>
                    </div>

                    <!-- Documents List -->
                    <div class="documents-list" id="documentsList">
                        @foreach($documents as $dokumen)
                            <div class="document-card clickable-card" data-id="{{ $dokumen->id }}"
                                data-agenda="{{ strtolower($dokumen->nomor_agenda) }}"
                                data-spp="{{ strtolower($dokumen->nomor_spp) }}"
                                data-uraian="{{ strtolower($dokumen->uraian_spp ?? '') }}"
                                data-nilai="{{ $dokumen->nilai_rupiah ?? 0 }}">
                                <div class="document-card-with-checkbox">
                                    <!-- Checkbox for bulk selection -->
                                    <div class="document-checkbox-wrapper">
                                        <label class="bulk-checkbox" onclick="event.stopPropagation();">
                                            <input type="checkbox" class="doc-checkbox" data-id="{{ $dokumen->id }}"
                                                data-agenda="{{ $dokumen->nomor_agenda }}" data-spp="{{ $dokumen->nomor_spp }}"
                                                data-nilai="{{ $dokumen->nilai_rupiah ?? 0 }}"
                                                onchange="handleDocCheckboxChange(this)">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <!-- Document content -->
                                    <div class="document-card-content"
                                        onclick="handleItemClick(event, '{{ route('inbox.show', $dokumen) }}')">
                                        <div class="document-card-header">
                                            <div class="document-number">
                                                <div class="document-agenda">
                                                    <i class="fas fa-file-invoice"></i>
                                                    <span>{{ $dokumen->nomor_agenda }}</span>
                                                </div>
                                                <div class="document-spp select-text">{{ $dokumen->nomor_spp }}</div>
                                            </div>
                                            <div class="d-flex align-items-center gap-2">
                                                @php
                                                    // Get sent_at from dokumen_statuses or dokumen_role_data
                                                    $currentRoleCode = strtolower($userRole ?? 'team_verifikasi');
                                                    $roleStatus = $dokumen->getStatusForRole($currentRoleCode);
                                                    $roleData = $dokumen->getDataForRole($currentRoleCode);
                                                    $sentAt = $roleStatus?->status_changed_at ?? $roleData?->received_at ?? null;
                                                    $isNew = $sentAt && $sentAt->gte(now()->subHours(24));
                                                @endphp
                                                @if($isNew)
                                                    <span class="new-badge">
                                                        <i class="fas fa-star"></i>
                                                        NEW
                                                    </span>
                                                @endif
                                                <div class="document-status-badge">
                                                    <i class="fas fa-clock"></i>
                                                    Menunggu
                                                </div>
                                            </div>
                                        </div>

                                        <div class="document-body">
                                            <div class="document-info-item">
                                                <div class="document-info-label">Uraian</div>
                                                <div class="document-info-value text-truncate-2">
                                                    {{ $dokumen->uraian_spp ?? '-' }}
                                                </div>
                                            </div>

                                            <div class="document-info-item">
                                                <div class="document-info-label">Pengirim</div>
                                                <div class="document-info-value">
                                                    <i class="fas fa-user"></i>
                                                    @php
                                                        $currentRoleCode = strtolower($userRole ?? '');
                                                        $senderName = $dokumen->getInboxSenderDisplayName($currentRoleCode);
                                                    @endphp
                                                    <span>{{ $senderName }}</span>
                                                </div>
                                            </div>

                                            <div class="document-info-item">
                                                <div class="document-info-label">Tanggal Kirim</div>
                                                <div class="document-info-value">
                                                    <i class="fas fa-calendar-alt"></i>
                                                    @php
                                                        $currentRoleCode = strtolower($userRole ?? 'team_verifikasi');
                                                        $roleStatus = $dokumen->getStatusForRole($currentRoleCode);
                                                        $roleData = $dokumen->getDataForRole($currentRoleCode);
                                                        $sentAt = $roleStatus?->status_changed_at ?? $roleData?->received_at ?? null;
                                                    @endphp
                                                    <span>{{ $sentAt ? $sentAt->format('d/m/Y H:i') : '-' }}</span>
                                                </div>
                                            </div>

                                            <div class="document-info-item">
                                                <div class="document-info-label">Nilai</div>
                                                <div class="document-info-value text-success">
                                                    <i class="fas fa-money-bill-wave"></i>
                                                    <span class="select-text">Rp
                                                        {{ number_format($dokumen->nilai_rupiah ?? 0, 0, ',', '.') }}</span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="document-footer">
                                            <div class="document-time">
                                                <i class="far fa-clock"></i>
                                                @php
                                                    $currentRoleCode = strtolower($userRole ?? 'team_verifikasi');
                                                    $roleStatus = $dokumen->getStatusForRole($currentRoleCode);
                                                    $roleData = $dokumen->getDataForRole($currentRoleCode);
                                                    $sentAt = $roleStatus?->status_changed_at ?? $roleData?->received_at ?? null;
                                                @endphp
                                                <span>Diterima {{ $sentAt ? $sentAt->diffForHumans() : '-' }}</span>
                                            </div>
                                            <a href="{{ route('inbox.show', $dokumen) }}" class="btn-view-detail"
                                                onclick="event.stopPropagation();">
                                                <i class="fas fa-eye"></i>
                                                Lihat Detail
                                            </a>
                                        </div>
                                    </div>
                                    <!-- end document-card-content -->
                                </div><!-- end document-card-with-checkbox -->
                            </div><!-- end document-card -->
                        @endforeach
                    </div>

                    <!-- Pagination -->
                    @if($documents->hasPages())
                        <div class="pagination-wrapper">
                            {{ $documents->links() }}
                        </div>
                    @endif
                @else
                    <!-- Empty State -->
                    <div class="empty-state">
                        <div class="empty-state-icon">
                            <i class="fas fa-check-circle" style="color: var(--accent-green);"></i>
                        </div>
                        <h3 class="empty-state-title">Semua Dokumen Telah Diproses! 🎉</h3>
                        <p class="empty-state-subtitle">
                            Tidak ada dokumen yang perlu diperiksa saat ini.
                            Anda telah menyelesaikan semua persetujuan dokumen dengan baik.
                        </p>
                        <button class="btn-refresh" onclick="window.location.reload()">
                            <i class="fas fa-sync-alt"></i>
                            Refresh Halaman
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Notification Toast Container -->
    <div id="notificationContainer"></div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Handle flash messages from server
            @if(session('success'))
                showNotification('success', 'Berhasil!', '{{ session('success') }}');
            @endif

            @if(session('error'))
                showNotification('error', 'Error!', '{{ session('error') }}');
            @endif

            @if(session('info'))
                showNotification('info', 'Informasi', '{{ session('info') }}');
            @endif

            // Initialize notification polling
            initNotificationPolling();

            const searchInput = document.getElementById('searchInput');
            const filterSelect = document.getElementById('filterSelect');
            const documentCards = document.querySelectorAll('.document-card');

            // Search functionality
            if (searchInput) {
                searchInput.addEventListener('input', function () {
                    const searchTerm = this.value.toLowerCase().trim();
                    filterDocuments(searchTerm, filterSelect?.value || '');
                });
            }

            // Filter functionality
            if (filterSelect) {
                filterSelect.addEventListener('change', function () {
                    const filterValue = this.value;
                    filterDocuments(searchInput?.value.toLowerCase().trim() || '', filterValue);
                });
            }

            function filterDocuments(searchTerm, filterValue) {
                let visibleCount = 0;

                documentCards.forEach(card => {
                    const agenda = card.getAttribute('data-agenda') || '';
                    const spp = card.getAttribute('data-spp') || '';
                    const uraian = card.getAttribute('data-uraian') || '';

                    const matchesSearch = !searchTerm ||
                        agenda.includes(searchTerm) ||
                        spp.includes(searchTerm) ||
                        uraian.includes(searchTerm);

                    // For now, all cards are pending, so filter only affects search
                    const matchesFilter = !filterValue || filterValue === 'pending';

                    if (matchesSearch && matchesFilter) {
                        card.style.display = 'block';
                        visibleCount++;
                    } else {
                        card.style.display = 'none';
                    }
                });

                // Show message if no results
                const documentsList = document.getElementById('documentsList');
                if (documentsList) {
                    let noResultsMsg = documentsList.querySelector('.no-results-message');
                    if (visibleCount === 0 && documentCards.length > 0) {
                        if (!noResultsMsg) {
                            noResultsMsg = document.createElement('div');
                            noResultsMsg.className = 'empty-state';
                            noResultsMsg.innerHTML = `
                                                                    <div class="empty-state-icon">
                                                                        <i class="fas fa-search" style="color: var(--text-muted);"></i>
                                                                    </div>
                                                                    <h3 class="empty-state-title">Tidak Ada Hasil</h3>
                                                                    <p class="empty-state-subtitle">Tidak ada dokumen yang sesuai dengan pencarian Anda.</p>
                                                                                           `;
                            documentsList.appendChild(noResultsMsg);
                        }
                    } else if (noResultsMsg) {
                        noResultsMsg.remove();
                    }
                }
            }

            // Add smooth scroll to top when clicking pagination
            document.querySelectorAll('.pagination a').forEach(link => {
                link.addEventListener('click', function () {
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                });
            });
        });

        // Notification Polling System
        function initNotificationPolling() {
            // Get last check time from localStorage
            let lastCheckTime = localStorage.getItem('inbox_last_check_time');
            if (!lastCheckTime) {
                // Set initial check time to now
                lastCheckTime = new Date().toISOString();
                localStorage.setItem('inbox_last_check_time', lastCheckTime);
            }

            // Function to check for new documents
            function checkNewDocuments() {
                fetch('{{ route("inbox.checkNew") }}?last_check_time=' + encodeURIComponent(lastCheckTime), {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                    credentials: 'same-origin'
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Update last check time
                            if (data.current_time) {
                                lastCheckTime = data.current_time;
                                localStorage.setItem('inbox_last_check_time', lastCheckTime);
                            }

                            // If there are new documents
                            if (data.new_documents_count > 0) {
                                // Update badge counter
                                updateNewDocumentsBadge(data.new_documents_count, data.pending_count);

                                // Track shown notifications to prevent duplicates
                                let shownNotificationIds = new Set(JSON.parse(localStorage.getItem('inbox_shown_notifications') || '[]'));

                                // Filter out already shown notifications
                                const newDocsToShow = data.new_documents.filter(doc => {
                                    if (shownNotificationIds.has(doc.id)) {
                                        return false;
                                    }
                                    shownNotificationIds.add(doc.id);
                                    return true;
                                });

                                // Save shown notification IDs to localStorage
                                localStorage.setItem('inbox_shown_notifications', JSON.stringify(Array.from(shownNotificationIds)));

                                // Show notification only for truly new documents
                                if (newDocsToShow.length > 0) {
                                    // Show notification for each new document (only show one at a time)
                                    if (newDocsToShow.length === 1) {
                                        showNewDocumentNotification(newDocsToShow[0]);
                                    } else {
                                        // If multiple new documents, show notification for the latest one
                                        showNewDocumentNotification(newDocsToShow[0]);
                                    }

                                    // Play notification sound only once
                                    playNotificationSound();
                                }
                            } else {
                                // Update badge even if no new documents (to sync count)
                                updateNewDocumentsBadge(0, data.pending_count);
                            }
                        }
                    })
                    .catch(error => {
                        console.error('Error checking new documents:', error);
                    });
            }

            // Check immediately on page load
            checkNewDocuments();

            // Poll every 30 seconds
            setInterval(checkNewDocuments, 30000);
        }

        // Update new documents badge
        function updateNewDocumentsBadge(newCount, pendingCount) {
            const badge = document.getElementById('newDocumentsBadge');
            const countElement = document.getElementById('newDocumentsCount');

            if (newCount > 0) {
                if (!badge) {
                    // Create badge if it doesn't exist
                    const documentsHeader = document.querySelector('.documents-header .col-md-4');
                    if (documentsHeader) {
                        const badgeHtml = `
                                                                <span class="new-documents-badge" id="newDocumentsBadge">
                                                                    <i class="fas fa-bell"></i>
                                                                    <span id="newDocumentsCount">${newCount}</span> Baru
                                                                </span>
                                                            `;
                        documentsHeader.querySelector('.d-flex').insertAdjacentHTML('afterbegin', badgeHtml);
                    }
                } else {
                    // Update existing badge
                    if (countElement) {
                        countElement.textContent = newCount;
                    }
                    badge.style.display = 'inline-flex';
                }
            } else {
                // Hide badge if no new documents
                if (badge) {
                    badge.style.display = 'none';
                }
            }
        }

        // Show notification for new document
        function showNewDocumentNotification(doc) {
            const message = `Dokumen baru: ${doc.nomor_agenda} - ${doc.uraian_spp}`;
            showNotificationWithAction('info', 'Dokumen Baru Masuk!', message, doc.url, 'Lihat Dokumen');
        }

        // Play notification sound
        function playNotificationSound() {
            // Create a simple beep sound using Web Audio API
            try {
                const audioContext = new (window.AudioContext || window.webkitAudioContext)();
                const oscillator = audioContext.createOscillator();
                const gainNode = audioContext.createGain();

                oscillator.connect(gainNode);
                gainNode.connect(audioContext.destination);

                oscillator.frequency.value = 800;
                oscillator.type = 'sine';

                gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
                gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.2);

                oscillator.start(audioContext.currentTime);
                oscillator.stop(audioContext.currentTime + 0.2);
            } catch (e) {
                // Fallback: browser notification if available
                if ('Notification' in window && Notification.permission === 'granted') {
                    new Notification('Dokumen Baru Masuk', {
                        body: 'Ada dokumen baru yang masuk ke inbox Anda',
                        icon: '/favicon.ico'
                    });
                }
            }
        }

        // Request notification permission on page load
        if ('Notification' in window && Notification.permission === 'default') {
            Notification.requestPermission();
        }

        // Notification Toast Function
        function showNotification(type, title, message) {
            let container = document.getElementById('notificationContainer');
            if (!container) {
                // Create container if it doesn't exist
                const newContainer = document.createElement('div');
                newContainer.id = 'notificationContainer';
                document.body.appendChild(newContainer);
                container = newContainer;
            }

            const toast = document.createElement('div');
            toast.className = `notification-toast ${type}`;

            const icons = {
                success: '<i class="fas fa-check-circle"></i>',
                error: '<i class="fas fa-times-circle"></i>',
                warning: '<i class="fas fa-exclamation-triangle"></i>',
                info: '<i class="fas fa-info-circle"></i>'
            };

            toast.innerHTML = `
                                                    <button class="notification-close" onclick="this.parentElement.remove()">&times;</button>
                                                    <div class="notification-content">
                                                        <div class="notification-icon">
                                                            ${icons[type] || icons.success}
                                                        </div>
                                                        <div class="notification-body">
                                                            <div class="notification-title">${title}</div>
                                                            <div class="notification-message">${message}</div>
                                                        </div>
                                                    </div>
                                                `;

            container.appendChild(toast);

            // Trigger animation
            setTimeout(() => {
                toast.classList.add('show');
            }, 10);

            // Auto-remove untuk notifikasi success/error biasa (dari session) setelah 4 detik
            // Notifikasi dokumen masuk/reject tetap permanen (dipanggil dengan showNotificationWithAction)
            if (type === 'success' || type === 'error') {
                setTimeout(() => {
                    toast.classList.add('hide');
                    setTimeout(() => {
                        if (toast.parentElement) {
                            toast.remove();
                        }
                    }, 300);
                }, 4000); // 4 detik untuk notifikasi success/error biasa
            }
            // Jika type info/warning atau dokumen masuk/reject, tetap permanen
        }

        // Notification Toast Function with Action Button
        function showNotificationWithAction(type, title, message, actionUrl, actionText) {
            let container = document.getElementById('notificationContainer');
            if (!container) {
                // Create container if it doesn't exist
                const newContainer = document.createElement('div');
                newContainer.id = 'notificationContainer';
                document.body.appendChild(newContainer);
                container = newContainer;
            }

            const toast = document.createElement('div');
            toast.className = `notification-toast ${type}`;

            const icons = {
                success: '<i class="fas fa-check-circle"></i>',
                error: '<i class="fas fa-times-circle"></i>',
                warning: '<i class="fas fa-exclamation-triangle"></i>',
                info: '<i class="fas fa-bell"></i>'
            };

            toast.innerHTML = `
                                                    <button class="notification-close" onclick="this.parentElement.remove()">&times;</button>
                                                    <div class="notification-content">
                                                        <div class="notification-icon">
                                                            ${icons[type] || icons.info}
                                                        </div>
                                                        <div class="notification-body">
                                                            <div class="notification-title">${title}</div>
                                                            <div class="notification-message">${message}</div>
                                                            ${actionUrl ? `<a href="${actionUrl}" class="notification-action-btn">${actionText || 'Lihat'}</a>` : ''}
                                                        </div>
                                                    </div>
                                                `;

            container.appendChild(toast);

            // Trigger animation
            setTimeout(() => {
                toast.classList.add('show');
            }, 10);

            // Notifikasi permanen - hanya hilang ketika user klik tombol X
            // Auto-remove dihapus agar notifikasi tetap muncul sampai user menutupnya
        }
    </script>

    <style>
        /* Modern Notification Toast Styles */
        #notificationContainer {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            pointer-events: none;
        }

        .notification-toast {
            min-width: 350px;
            max-width: 500px;
            background: white;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
            padding: 0;
            overflow: hidden;
            animation: slideInRight 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            transform: translateX(400px);
            opacity: 0;
            margin-bottom: 16px;
            pointer-events: auto;
        }

        .notification-toast.show {
            transform: translateX(0);
            opacity: 1;
        }

        .notification-toast.hide {
            animation: slideOutRight 0.3s ease-in forwards;
        }

        @keyframes slideInRight {
            from {
                transform: translateX(400px);
                opacity: 0;
            }

            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @keyframes slideOutRight {
            from {
                transform: translateX(0);
                opacity: 1;
            }

            to {
                transform: translateX(400px);
                opacity: 0;
            }
        }

        .notification-toast.success {
            border-left: 5px solid #48bb78;
        }

        .notification-toast.error {
            border-left: 5px solid #f56565;
        }

        .notification-toast.warning {
            border-left: 5px solid #f6ad55;
        }

        .notification-toast.info {
            border-left: 5px solid #4299e1;
        }

        .notification-content {
            padding: 20px;
            display: flex;
            align-items: flex-start;
            gap: 16px;
        }

        .notification-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            flex-shrink: 0;
        }

        .notification-toast.success .notification-icon {
            background: linear-gradient(135deg, #48bb78 0%, #9ae6b4 100%);
            color: white;
        }

        .notification-toast.error .notification-icon {
            background: linear-gradient(135deg, #f56565 0%, #fc8181 100%);
            color: white;
        }

        .notification-toast.warning .notification-icon {
            background: linear-gradient(135deg, #f6ad55 0%, #fed7aa 100%);
            color: white;
        }

        .notification-toast.info .notification-icon {
            background: linear-gradient(135deg, #4299e1 0%, #90cdf4 100%);
            color: white;
        }

        .notification-body {
            flex: 1;
        }

        .notification-title {
            font-weight: 700;
            font-size: 16px;
            margin-bottom: 4px;
            color: #1a202c;
        }

        .notification-message {
            font-size: 14px;
            color: #4a5568;
            line-height: 1.5;
            margin-bottom: 8px;
        }

        .notification-action-btn {
            display: inline-block;
            margin-top: 8px;
            padding: 8px 16px;
            background: linear-gradient(135deg, #4299e1 0%, #63b3ed 100%);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            transition: all 0.2s ease;
            box-shadow: 0 2px 6px rgba(66, 153, 225, 0.25);
        }

        .notification-action-btn:hover {
            background: linear-gradient(135deg, #3182ce 0%, #4299e1 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 10px rgba(66, 153, 225, 0.35);
            color: white;
            text-decoration: none;
        }

        .notification-close {
            position: absolute;
            top: 12px;
            right: 12px;
            background: none;
            border: none;
            color: #718096;
            font-size: 18px;
            cursor: pointer;
            padding: 4px;
            line-height: 1;
            transition: color 0.2s;
            z-index: 10;
        }

        .notification-close:hover {
            color: #2d3748;
        }

        /* ============================================
                                       MULTI-SELECT BULK APPROVE STYLES
                                       ============================================ */

        /* Select All Header */
        .select-all-container {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1rem;
            background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%);
            border-bottom: 1px solid var(--border-color);
            border-radius: 8px 8px 0 0;
            margin-bottom: 0;
        }

        /* Custom Checkbox Styling */
        .bulk-checkbox {
            position: relative;
            width: 22px;
            height: 22px;
            cursor: pointer;
            flex-shrink: 0;
        }

        .bulk-checkbox input[type="checkbox"] {
            position: absolute;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
            z-index: 2;
            margin: 0;
        }

        .bulk-checkbox .checkmark {
            position: absolute;
            top: 0;
            left: 0;
            width: 22px;
            height: 22px;
            background: white;
            border: 2px solid #cbd5e0;
            border-radius: 6px;
            transition: all 0.2s ease;
        }

        .bulk-checkbox:hover .checkmark {
            border-color: var(--accent-blue);
            background: #ebf8ff;
        }

        .bulk-checkbox input[type="checkbox"]:checked+.checkmark {
            background: linear-gradient(135deg, var(--accent-blue) 0%, #63b3ed 100%);
            border-color: var(--accent-blue);
        }

        .bulk-checkbox .checkmark::after {
            content: '';
            position: absolute;
            display: none;
            left: 7px;
            top: 3px;
            width: 5px;
            height: 10px;
            border: solid white;
            border-width: 0 2px 2px 0;
            transform: rotate(45deg);
        }

        .bulk-checkbox input[type="checkbox"]:checked+.checkmark::after {
            display: block;
        }

        .select-all-label {
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--text-secondary);
            cursor: pointer;
            user-select: none;
        }

        .selected-count-badge {
            background: linear-gradient(135deg, var(--accent-blue) 0%, #63b3ed 100%);
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            display: none;
        }

        .selected-count-badge.show {
            display: inline-flex;
        }

        /* Document card checkbox positioning */
        .document-card-with-checkbox {
            display: flex;
            gap: 1rem;
            align-items: flex-start;
        }

        .document-checkbox-wrapper {
            padding-top: 0.25rem;
        }

        .document-card-content {
            flex: 1;
            min-width: 0;
        }

        /* Selected state for document card */
        .document-card.selected {
            border-color: var(--accent-blue);
            background: linear-gradient(135deg, #ebf8ff 0%, #f7fafc 100%);
            box-shadow: 0 0 0 3px rgba(66, 153, 225, 0.15);
        }

        .document-card.selected::before {
            transform: scaleY(1);
            background: var(--accent-blue);
        }

        /* ============================================
                                       FLOATING ACTION BAR
                                       ============================================ */
        .bulk-action-bar {
            position: fixed;
            bottom: -100px;
            left: 50%;
            transform: translateX(-50%);
            background: linear-gradient(135deg, #2d3748 0%, #1a202c 100%);
            padding: 1rem 2rem;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
            display: flex;
            align-items: center;
            gap: 1.5rem;
            z-index: 1000;
            transition: bottom 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        .bulk-action-bar.show {
            bottom: 30px;
        }

        .bulk-action-info {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            color: white;
        }

        .bulk-action-icon {
            width: 40px;
            height: 40px;
            background: rgba(66, 153, 225, 0.2);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #63b3ed;
            font-size: 1.125rem;
        }

        .bulk-action-text {
            font-size: 0.9375rem;
            font-weight: 600;
        }

        .bulk-action-text span {
            color: #63b3ed;
        }

        .bulk-action-buttons {
            display: flex;
            gap: 0.75rem;
        }

        .btn-bulk-approve {
            background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 10px;
            font-weight: 600;
            font-size: 0.875rem;
            border: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.2s ease;
            box-shadow: 0 4px 12px rgba(72, 187, 120, 0.3);
        }

        .btn-bulk-approve:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(72, 187, 120, 0.4);
        }

        .btn-bulk-approve:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .btn-bulk-cancel {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            padding: 0.75rem 1.25rem;
            border-radius: 10px;
            font-weight: 500;
            font-size: 0.875rem;
            border: 1px solid rgba(255, 255, 255, 0.2);
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .btn-bulk-cancel:hover {
            background: rgba(255, 255, 255, 0.15);
        }

        /* ============================================
                                       BULK APPROVE CONFIRMATION MODAL
                                       ============================================ */
        .bulk-modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(4px);
            z-index: 2000;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }

        .bulk-modal-overlay.active {
            display: flex;
            animation: fadeIn 0.2s ease;
        }

        .bulk-modal {
            background: white;
            border-radius: 16px;
            width: 100%;
            max-width: 600px;
            max-height: 85vh;
            overflow: hidden;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.25);
            animation: slideUp 0.3s ease;
        }

        .bulk-modal-header {
            padding: 1.5rem 1.75rem;
            background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
            color: white;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .bulk-modal-title {
            font-size: 1.25rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin: 0;
        }

        .bulk-modal-close {
            background: rgba(255, 255, 255, 0.2);
            border: none;
            color: white;
            width: 36px;
            height: 36px;
            border-radius: 10px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
        }

        .bulk-modal-close:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        .bulk-modal-body {
            padding: 1.5rem 1.75rem;
            max-height: 400px;
            overflow-y: auto;
        }

        .bulk-modal-summary {
            background: #f7fafc;
            border-radius: 10px;
            padding: 1rem 1.25rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .bulk-modal-summary-icon {
            width: 44px;
            height: 44px;
            background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.25rem;
        }

        .bulk-modal-summary-text {
            flex: 1;
        }

        .bulk-modal-summary-count {
            font-size: 1.5rem;
            font-weight: 700;
            color: #2d3748;
        }

        .bulk-modal-summary-label {
            font-size: 0.8125rem;
            color: #718096;
        }

        .bulk-modal-doc-list {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .bulk-modal-doc-item {
            background: #f7fafc;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 0.75rem 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .bulk-modal-doc-info {
            display: flex;
            flex-direction: column;
            gap: 0.125rem;
        }

        .bulk-modal-doc-agenda {
            font-weight: 600;
            color: var(--accent-blue);
            font-size: 0.875rem;
        }

        .bulk-modal-doc-spp {
            font-size: 0.75rem;
            color: #718096;
        }

        .bulk-modal-doc-value {
            font-weight: 600;
            color: #2d3748;
            font-size: 0.875rem;
        }

        .bulk-modal-footer {
            padding: 1.25rem 1.75rem;
            background: #f7fafc;
            border-top: 1px solid var(--border-color);
            display: flex;
            justify-content: flex-end;
            gap: 0.75rem;
        }

        .btn-modal-cancel {
            background: white;
            color: #4a5568;
            padding: 0.75rem 1.5rem;
            border-radius: 10px;
            font-weight: 600;
            font-size: 0.875rem;
            border: 1.5px solid var(--border-color);
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .btn-modal-cancel:hover {
            background: #f7fafc;
            border-color: #cbd5e0;
        }

        .btn-modal-confirm {
            background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 10px;
            font-weight: 600;
            font-size: 0.875rem;
            border: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.2s ease;
            box-shadow: 0 4px 12px rgba(72, 187, 120, 0.3);
        }

        .btn-modal-confirm:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(72, 187, 120, 0.4);
        }

        .btn-modal-confirm:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .btn-modal-confirm .spinner {
            display: none;
            animation: spin 1s linear infinite;
        }

        .btn-modal-confirm.loading .spinner {
            display: inline-block;
        }

        .btn-modal-confirm.loading .btn-text {
            display: none;
        }
    </style>

    <!-- History Modal -->
    <div class="history-modal-overlay" id="historyModalOverlay" onclick="closeHistoryModalOnOverlay(event)">
        <div class="history-modal">
            <div class="history-modal-header">
                <h3 class="history-modal-title">
                    <i class="fas fa-history"></i>
                    Riwayat Inbox
                </h3>
                <button class="history-modal-close" onclick="closeHistoryModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="history-modal-filters">
                <div class="filter-buttons">
                    <button class="filter-btn active" data-filter="today" onclick="loadHistoryByFilter('today', this)">Hari
                        Ini</button>
                    <button class="filter-btn" data-filter="yesterday"
                        onclick="loadHistoryByFilter('yesterday', this)">Kemarin</button>
                    <button class="filter-btn" data-filter="3days" onclick="loadHistoryByFilter('3days', this)">3
                        Hari
                        Lalu</button>
                    <button class="filter-btn" data-filter="7days" onclick="loadHistoryByFilter('7days', this)">7
                        Hari
                        Lalu</button>
                </div>
                <div class="custom-date-wrapper">
                    <label for="historyCustomDate">Atau pilih tanggal:</label>
                    <input type="date" id="historyCustomDate" onchange="loadHistoryByCustomDate(this.value)">
                </div>
            </div>
            <div class="history-modal-body" id="historyModalBody">
                <div class="history-loading">
                    <i class="fas fa-spinner"></i>
                    <p>Memuat data...</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        // History Modal Functions
        function openHistoryModal() {
            const overlay = document.getElementById('historyModalOverlay');
            overlay.classList.add('active');
            document.body.style.overflow = 'hidden';

            // Reset filters
            document.querySelectorAll('.filter-btn').forEach(btn => btn.classList.remove('active'));
            document.querySelector('.filter-btn[data-filter="today"]').classList.add('active');
            document.getElementById('historyCustomDate').value = '';

            // Load today's data
            loadHistoryByFilter('today');
        }

        function closeHistoryModal() {
            const overlay = document.getElementById('historyModalOverlay');
            overlay.classList.remove('active');
            document.body.style.overflow = '';
        }

        function closeHistoryModalOnOverlay(event) {
            if (event.target === event.currentTarget) {
                closeHistoryModal();
            }
        }

        function loadHistoryByFilter(filter, button = null) {
            // Update active state
            if (button) {
                document.querySelectorAll('.filter-btn').forEach(btn => btn.classList.remove('active'));
                button.classList.add('active');
            }
            document.getElementById('historyCustomDate').value = '';

            loadHistoryData(filter);
        }

        function loadHistoryByCustomDate(date) {
            if (!date) return;

            // Remove active from filter buttons
            document.querySelectorAll('.filter-btn').forEach(btn => btn.classList.remove('active'));

            loadHistoryData(date);
        }

        function loadHistoryData(dateFilter) {
            const body = document.getElementById('historyModalBody');
            body.innerHTML = `
                                                        <div class="history-loading">
                                                            <i class="fas fa-spinner"></i>
                                                            <p>Memuat data...</p>
                                                        </div>
                                                    `;

            fetch(`{{ route('inbox.history') }}?date=${encodeURIComponent(dateFilter)}`, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
                credentials: 'same-origin'
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        renderHistoryDocuments(data);
                    } else {
                        body.innerHTML = `
                                                                <div class="history-empty-state">
                                                                    <i class="fas fa-exclamation-circle"></i>
                                                                    <p>${data.message || 'Gagal memuat data'}</p>
                                                                </div>
                                                            `;
                    }
                })
                .catch(error => {
                    console.error('Error loading history:', error);
                    body.innerHTML = `
                                                            <div class="history-empty-state">
                                                                <i class="fas fa-exclamation-triangle"></i>
                                                                <p>Terjadi kesalahan saat memuat data</p>
                                                            </div>
                                                        `;
                });
        }

        function renderHistoryDocuments(data) {
            const body = document.getElementById('historyModalBody');

            if (data.documents_count === 0) {
                body.innerHTML = `
                                                            <div class="history-date-label">
                                                                ${data.date_label}
                                                                <span class="count-badge">0 dokumen</span>
                                                            </div>
                                                            <div class="history-empty-state">
                                                                <i class="fas fa-inbox"></i>
                                                                <p>Tidak ada dokumen yang masuk pada periode ini</p>
                                                            </div>
                                                        `;
                return;
            }

            let html = `
                                                        <div class="history-date-label">
                                                            ${data.date_label}
                                                            <span class="count-badge">${data.documents_count} dokumen</span>
                                                        </div>
                                                        <div class="history-doc-list">
                                                    `;

            data.documents.forEach(doc => {
                const statusClass = doc.status || 'pending';
                html += `
                                                            <div class="history-doc-item" onclick="window.location.href='${doc.url}'">
                                                                <div class="history-doc-header">
                                                                    <span class="history-doc-agenda">${doc.nomor_agenda}</span>
                                                                    <span class="history-doc-status ${statusClass}">${doc.status_label}</span>
                                                                </div>
                                                                <div class="history-doc-spp">${doc.nomor_spp}</div>
                                                                <div class="history-doc-uraian">${doc.uraian_spp}</div>
                                                                <div class="history-doc-footer">
                                                                    <span class="history-doc-time">
                                                                        <i class="fas fa-clock"></i>
                                                                        Masuk: ${doc.received_at}
                                                                    </span>
                                                                    <span class="history-doc-value">${doc.nilai_rupiah}</span>
                                                                </div>
                                                            </div>
                                                        `;
            });

            html += '</div>';
            body.innerHTML = html;
        }

        // Close modal on Escape key
        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                closeHistoryModal();
                closeBulkApproveModal();
            }
        });
    </script>

    <!-- Floating Action Bar for Bulk Approve -->
    <div class="bulk-action-bar" id="bulkActionBar">
        <div class="bulk-action-info">
            <div class="bulk-action-icon">
                <i class="fas fa-check-double"></i>
            </div>
            <div class="bulk-action-text">
                <span id="bulkSelectedCount">0</span> dokumen dipilih
            </div>
        </div>
        <div class="bulk-action-buttons">
            <button type="button" class="btn-bulk-cancel" onclick="clearAllSelections()">
                <i class="fas fa-times"></i> Batal
            </button>
            <button type="button" class="btn-bulk-approve" onclick="showBulkApproveModal()">
                <i class="fas fa-check"></i> Approve Semua
            </button>
        </div>
    </div>

    <!-- Bulk Approve Confirmation Modal -->
    <div class="bulk-modal-overlay" id="bulkApproveModalOverlay" onclick="closeBulkApproveModalOnOverlay(event)">
        <div class="bulk-modal">
            <div class="bulk-modal-header">
                <h3 class="bulk-modal-title">
                    <i class="fas fa-check-double"></i>
                    Konfirmasi Bulk Approve
                </h3>
                <button class="bulk-modal-close" onclick="closeBulkApproveModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="bulk-modal-body">
                <div class="bulk-modal-summary">
                    <div class="bulk-modal-summary-icon">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <div class="bulk-modal-summary-text">
                        <div class="bulk-modal-summary-count" id="modalSelectedCount">0</div>
                        <div class="bulk-modal-summary-label">Dokumen akan disetujui</div>
                    </div>
                </div>
                <div class="bulk-modal-doc-list" id="modalDocList">
                    <!-- Document list populated dynamically -->
                </div>
            </div>
            <div class="bulk-modal-footer">
                <button type="button" class="btn-modal-cancel" onclick="closeBulkApproveModal()">
                    Batal
                </button>
                <button type="button" class="btn-modal-confirm" id="btnConfirmBulkApprove" onclick="executeBulkApprove()">
                    <i class="fas fa-spinner spinner"></i>
                    <span class="btn-text"><i class="fas fa-check"></i> Ya, Setujui Semua</span>
                </button>
            </div>
        </div>
    </div>

    <script>
        // ============================================
        // BULK APPROVE MULTI-SELECT FUNCTIONALITY
        // ============================================

        let selectedDocuments = new Map();

        function toggleSelectAll() {
            const selectAllCheckbox = document.getElementById('selectAllCheckbox');
            const isChecked = selectAllCheckbox.checked;
            const docCheckboxes = document.querySelectorAll('.doc-checkbox');

            console.log('toggleSelectAll called:', isChecked, 'Found checkboxes:', docCheckboxes.length);

            // Clear existing selections first if unchecking
            if (!isChecked) {
                selectedDocuments.clear();
            }

            docCheckboxes.forEach(function (checkbox) {
                checkbox.checked = isChecked;
                const docId = checkbox.dataset.id;
                const docCard = checkbox.closest('.document-card');

                if (isChecked) {
                    selectedDocuments.set(docId, {
                        id: docId,
                        agenda: checkbox.dataset.agenda,
                        spp: checkbox.dataset.spp,
                        nilai: parseFloat(checkbox.dataset.nilai) || 0
                    });
                    docCard?.classList.add('selected');
                } else {
                    selectedDocuments.delete(docId);
                    docCard?.classList.remove('selected');
                }
            });

            updateSelectedCount();
            console.log('Selected documents:', selectedDocuments.size);
        }

        function handleDocCheckboxChange(checkbox, updateCount = true) {
            const docId = checkbox.dataset.id;
            const docCard = checkbox.closest('.document-card');

            if (checkbox.checked) {
                selectedDocuments.set(docId, {
                    id: docId,
                    agenda: checkbox.dataset.agenda,
                    spp: checkbox.dataset.spp,
                    nilai: parseFloat(checkbox.dataset.nilai) || 0
                });
                docCard?.classList.add('selected');
            } else {
                selectedDocuments.delete(docId);
                docCard?.classList.remove('selected');
            }

            if (updateCount) {
                updateSelectedCount();
            }

            updateSelectAllState();
        }

        function updateSelectAllState() {
            const selectAllCheckbox = document.getElementById('selectAllCheckbox');
            const docCheckboxes = document.querySelectorAll('.doc-checkbox');
            const checkedCount = document.querySelectorAll('.doc-checkbox:checked').length;

            if (checkedCount === 0) {
                selectAllCheckbox.checked = false;
                selectAllCheckbox.indeterminate = false;
            } else if (checkedCount === docCheckboxes.length) {
                selectAllCheckbox.checked = true;
                selectAllCheckbox.indeterminate = false;
            } else {
                selectAllCheckbox.checked = false;
                selectAllCheckbox.indeterminate = true;
            }
        }

        function updateSelectedCount() {
            const count = selectedDocuments.size;
            const actionBar = document.getElementById('bulkActionBar');
            const countBadge = document.getElementById('selectedCountBadge');
            const bulkCountSpan = document.getElementById('bulkSelectedCount');

            countBadge.textContent = count + ' dipilih';
            bulkCountSpan.textContent = count;

            if (count > 0) {
                actionBar.classList.add('show');
                countBadge.classList.add('show');
            } else {
                actionBar.classList.remove('show');
                countBadge.classList.remove('show');
            }
        }

        function clearAllSelections() {
            selectedDocuments.clear();

            document.querySelectorAll('.doc-checkbox').forEach(checkbox => {
                checkbox.checked = false;
            });

            document.querySelectorAll('.document-card.selected').forEach(card => {
                card.classList.remove('selected');
            });

            document.getElementById('selectAllCheckbox').checked = false;
            document.getElementById('selectAllCheckbox').indeterminate = false;

            updateSelectedCount();
        }

        function showBulkApproveModal() {
            if (selectedDocuments.size === 0) {
                alert('Pilih minimal 1 dokumen untuk di-approve');
                return;
            }

            const overlay = document.getElementById('bulkApproveModalOverlay');
            const modalDocList = document.getElementById('modalDocList');
            const modalCount = document.getElementById('modalSelectedCount');

            modalCount.textContent = selectedDocuments.size;

            let html = '';
            selectedDocuments.forEach((doc, id) => {
                const formattedNilai = new Intl.NumberFormat('id-ID', {
                    style: 'currency',
                    currency: 'IDR',
                    minimumFractionDigits: 0
                }).format(doc.nilai);

                html += `
                                <div class="bulk-modal-doc-item">
                                    <div class="bulk-modal-doc-info">
                                        <div class="bulk-modal-doc-agenda">${doc.agenda}</div>
                                        <div class="bulk-modal-doc-spp">${doc.spp}</div>
                                    </div>
                                    <div class="bulk-modal-doc-value">${formattedNilai}</div>
                                </div>
                            `;
            });

            modalDocList.innerHTML = html;
            overlay.classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeBulkApproveModal() {
            const overlay = document.getElementById('bulkApproveModalOverlay');
            overlay.classList.remove('active');
            document.body.style.overflow = '';

            const confirmBtn = document.getElementById('btnConfirmBulkApprove');
            confirmBtn.classList.remove('loading');
            confirmBtn.disabled = false;
        }

        function closeBulkApproveModalOnOverlay(event) {
            if (event.target === event.currentTarget) {
                closeBulkApproveModal();
            }
        }

        function executeBulkApprove() {
            const confirmBtn = document.getElementById('btnConfirmBulkApprove');

            if (selectedDocuments.size === 0) {
                alert('Tidak ada dokumen yang dipilih');
                return;
            }

            confirmBtn.classList.add('loading');
            confirmBtn.disabled = true;

            const documentIds = Array.from(selectedDocuments.keys()).map(id => parseInt(id));

            fetch('{{ route("inbox.bulk-approve") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin',
                body: JSON.stringify({
                    document_ids: documentIds
                })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showBulkNotification('success', data.message);
                        closeBulkApproveModal();

                        selectedDocuments.forEach((doc, id) => {
                            const card = document.querySelector(`.document-card[data-id="${id}"]`);
                            if (card) {
                                card.style.transition = 'opacity 0.3s, transform 0.3s';
                                card.style.opacity = '0';
                                card.style.transform = 'translateX(-20px)';
                                setTimeout(() => card.remove(), 300);
                            }
                        });

                        setTimeout(() => {
                            clearAllSelections();
                            setTimeout(() => window.location.reload(), 1500);
                        }, 400);

                    } else {
                        showBulkNotification('error', data.message || 'Terjadi kesalahan');
                        confirmBtn.classList.remove('loading');
                        confirmBtn.disabled = false;
                    }
                })
                .catch(error => {
                    console.error('Bulk approve error:', error);
                    showBulkNotification('error', 'Terjadi kesalahan saat memproses bulk approve');
                    confirmBtn.classList.remove('loading');
                    confirmBtn.disabled = false;
                });
        }

        function showBulkNotification(type, message) {
            const existingNotif = document.querySelector('.bulk-notification');
            if (existingNotif) existingNotif.remove();

            const notifDiv = document.createElement('div');
            notifDiv.className = `bulk-notification ${type}`;
            notifDiv.style.cssText = `
                            position: fixed;
                            top: 20px;
                            right: 20px;
                            padding: 1rem 1.5rem;
                            border-radius: 12px;
                            color: white;
                            font-weight: 600;
                            z-index: 3000;
                            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
                            animation: slideInRight 0.3s ease;
                            display: flex;
                            align-items: center;
                            gap: 0.75rem;
                        `;

            const icon = type === 'success' ? 'fas fa-check-circle' : 'fas fa-exclamation-circle';
            const bgColor = type === 'success' ? 'linear-gradient(135deg, #48bb78, #38a169)' : 'linear-gradient(135deg, #f56565, #e53e3e)';
            notifDiv.style.background = bgColor;

            notifDiv.innerHTML = `<i class="${icon}"></i><span>${message}</span>`;
            document.body.appendChild(notifDiv);

            setTimeout(() => {
                notifDiv.style.opacity = '0';
                notifDiv.style.transform = 'translateX(20px)';
                setTimeout(() => notifDiv.remove(), 300);
            }, 4000);
        }

        // Add animation style
        const bulkStyle = document.createElement('style');
        bulkStyle.textContent = `
                        @keyframes slideInRight {
                            from { opacity: 0; transform: translateX(20px); }
                            to { opacity: 1; transform: translateX(0); }
                        }
                    `;
        document.head.appendChild(bulkStyle);
    </script>
@endsection