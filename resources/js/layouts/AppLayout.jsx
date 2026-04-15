import React from 'react';
import { Link } from '@inertiajs/react';

export default function AppLayout({ children, user }) {
    return (
        <div className="min-h-screen bg-gray-100 dark:bg-gray-900">
            <nav className="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div className="flex justify-between h-16">
                        <div className="flex">
                            <div className="flex-shrink-0 flex items-center">
                                <Link href="/" className="text-xl font-bold text-gray-800 dark:text-white">
                                    Library
                                </Link>
                            </div>
                        </div>
                        <div className="flex items-center">
                            <div className="flex items-center gap-4">
                                <span className="text-sm text-gray-700 dark:text-gray-300">
                                    {user?.name}
                                </span>
                                <Link
                                    href={route('logout')}
                                    method="post"
                                    as="button"
                                    className="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300"
                                >
                                    Log Out
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </nav>

            <main>
                {children}
            </main>
        </div>
    );
}