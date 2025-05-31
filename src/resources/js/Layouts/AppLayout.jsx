import React from 'react';
import { Link, usePage } from '@inertiajs/react';
import MainLayout from '@/Layouts/MainLayout';

export default function AppLayout({ children }) {
    const { auth } = usePage().props;
    const user = auth?.user;

    const navigationLinks = (
        <>
            <Link
                href="/games"
                className="inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-out"
            >
                Games
            </Link>
            <Link
                href="/about"
                className="inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-out"
            >
                About
            </Link>
            <Link
                href="/contact"
                className="inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-out"
            >
                Contact
            </Link>
        </>
    );

    const rightSideNavigation = user ? (
        <div className="ml-3 relative">
            <div className="flex items-center space-x-4">
                <Link
                    href="/profile"
                    className="text-sm text-gray-500 hover:text-gray-700"
                >
                    {user.name}
                </Link>
                <Link
                    href="/logout"
                    method="post"
                    as="button"
                    className="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150"
                >
                    Logout
                </Link>
            </div>
        </div>
    ) : (
        <div className="flex items-center space-x-4">
            <Link
                href="/login"
                className="text-sm text-gray-500 hover:text-gray-700"
            >
                Log in
            </Link>
            <Link
                href="/register"
                className="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
            >
                Register
            </Link>
        </div>
    );

    return (
        <MainLayout
            user={user}
            navigationLinks={navigationLinks}
            rightSideNavigation={rightSideNavigation}
        >
            {children}
        </MainLayout>
    );
} 