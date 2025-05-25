import React, { useState, useEffect } from 'react';
import { Link, usePage } from '@inertiajs/react';
import Sidebar from '@/Components/Admin/Sidebar';
import TopBar from '@/Components/Admin/TopBar';

export default function AdminLayout({ 
    title, 
    children
}) {
    const page = usePage();
    const { auth, url = '', flash } = page.props;
    const user = auth?.user;

    const [sidebarOpen, setSidebarOpen] = useState(false);
    const [showToast, setShowToast] = useState(false);
    const [toastMessage, setToastMessage] = useState('');
    const [toastType, setToastType] = useState('error');
    const [expandedMenus, setExpandedMenus] = useState({
        questions: url.startsWith('/admin/questions')
    });

    const toggleMenu = (menuName) => {
        setExpandedMenus(prev => ({
            ...prev,
            [menuName]: !prev[menuName]
        }));
    };

    useEffect(() => {
        // Log flash messages for debugging
        console.log('Flash messages:', flash);

        // Show toast if there's a flash message
        if (flash?.error || flash?.success || flash?.message) {
            setToastMessage(flash.error || flash.success || flash.message);
            setToastType(flash.error ? 'error' : 'success');
            setShowToast(true);

            // Auto-hide toast after 4 seconds
            const timer = setTimeout(() => {
                setShowToast(false);
            }, 4000);

            return () => clearTimeout(timer);
        }
    }, [flash]);

    const handleCloseToast = () => {
        setShowToast(false);
    };

    // Log user data for debugging
    console.log('User data:', user);
    console.log('Auth data:', auth);

    return (
        <div className="min-h-screen bg-gray-100">
            {/* Toast Notification */}
            {showToast && (
                <div className="fixed top-4 right-4 z-50 animate-fade-in-down">
                    <div className={`rounded-lg p-4 shadow-lg ${
                        toastType === 'error' ? 'bg-red-50 text-red-800' : 'bg-green-50 text-green-800'
                    }`}>
                        <div className="flex items-center">
                            <div className="flex-shrink-0">
                                {toastType === 'error' ? (
                                    <svg className="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                        <path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clipRule="evenodd" />
                                    </svg>
                                ) : (
                                    <svg className="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                                        <path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clipRule="evenodd" />
                                    </svg>
                                )}
                            </div>
                            <div className="ml-3">
                                <p className="text-sm font-medium">{toastMessage}</p>
                            </div>
                            <div className="ml-auto pl-3">
                                <button
                                    onClick={handleCloseToast}
                                    className="inline-flex rounded-md p-1.5 focus:outline-none focus:ring-2 focus:ring-offset-2"
                                >
                                    <span className="sr-only">Dismiss</span>
                                    <svg className="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                        <path fillRule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clipRule="evenodd" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            )}

            <Sidebar 
                url={url}
                user={user}
                expandedMenus={expandedMenus}
                toggleMenu={toggleMenu}
                isMobile={sidebarOpen}
                onClose={() => setSidebarOpen(false)}
            />

            <div className="lg:pl-64">
                <TopBar 
                    title={title} 
                    user={user} 
                    onMenuClick={() => setSidebarOpen(true)} 
                />

                <main className="py-6">
                    <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                        {children}
                    </div>
                </main>
            </div>
        </div>
    );
}

// Add this to your CSS (tailwind.config.js or a CSS file)
/*
@keyframes fade-in-down {
    0% {
        opacity: 0;
        transform: translateY(-1rem);
    }
    100% {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-fade-in-down {
    animation: fade-in-down 0.3s ease-out;
}
*/