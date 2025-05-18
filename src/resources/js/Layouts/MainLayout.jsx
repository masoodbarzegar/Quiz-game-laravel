import React from 'react';
import { Link } from '@inertiajs/react';

const MainLayout = ({ children }) => {
    return (
        <div className="min-h-screen bg-gray-50">
            <nav className="bg-white border-b border-gray-100 shadow-sm">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div className="flex justify-between h-16">
                        <div className="flex">
                            {/* Logo */}
                            <div className="flex-shrink-0 flex items-center">
                                <span className="text-xl font-bold text-blue-600">QuizGame</span>
                            </div>
                            
                            {/* Navigation Links */}
                            <div className="hidden sm:ml-6 sm:flex sm:space-x-8">
                                <Link 
                                    href="/" 
                                    className="inline-flex items-center px-1 pt-1 text-sm font-medium text-gray-900 border-b-2 border-blue-500"
                                >
                                    Home
                                </Link>
                                <Link 
                                    href="/about" 
                                    className="inline-flex items-center px-1 pt-1 text-sm font-medium text-gray-500 hover:text-gray-900 hover:border-gray-300 border-b-2 border-transparent"
                                >
                                    About
                                </Link>
                                <Link 
                                    href="/contact" 
                                    className="inline-flex items-center px-1 pt-1 text-sm font-medium text-gray-500 hover:text-gray-900 hover:border-gray-300 border-b-2 border-transparent"
                                >
                                    Contact
                                </Link>
                            </div>
                        </div>

                        {/* Right side buttons */}
                        <div className="hidden sm:ml-6 sm:flex sm:items-center space-x-4">
                            <button className="btn bg-gray-100 text-gray-700 hover:bg-gray-200">
                                Sign In
                            </button>
                            <button className="btn">
                                Sign Up
                            </button>
                        </div>
                    </div>
                </div>
            </nav>

            <main className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    {children}
                </div>
            </main>

            {/* Footer */}
            <footer className="bg-white border-t border-gray-100 mt-12">
                <div className="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    <p className="text-center text-gray-500 text-sm">
                        © 2024 QuizGame. All rights reserved.
                    </p>
                </div>
            </footer>
        </div>
    );
};

export default MainLayout; 