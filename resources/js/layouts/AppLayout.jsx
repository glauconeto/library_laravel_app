import React from 'react';
import { Link } from '@inertiajs/react';
import FlashMessage from '../Components/FlashMessage';

export default function AppLayout({ children, user }) {
    const isLibrarian = user?.roles?.some(role => role.name === 'librarian');

    const navLinks = [
        { href: route('dashboard'), label: 'Painel' },
        { href: route('books.index'), label: 'Livros' },
        { href: route('loans.index'), label: 'Empréstimos' },
        { href: route('feedbacks.index'), label: 'Avaliações' },
    ];

    return (
        <div className="min-h-screen bg-gray-100 dark:bg-gray-900">
            <nav className="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div className="flex justify-between h-16">
                        <div className="flex items-center">
                            <div className="flex-shrink-0">
                                <Link href="/" className="text-xl font-bold text-indigo-600 dark:text-indigo-400">
                                    📚 Biblioteca
                                </Link>
                            </div>
                            <div className="hidden sm:ml-6 sm:flex sm:space-x-8">
                                {navLinks.map((link) => (
                                    <Link
                                        key={link.href}
                                        href={link.href}
                                        className="inline-flex items-center px-1 pt-1 text-sm font-medium text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 border-b-2 border-transparent hover:border-indigo-500 transition duration-150"
                                    >
                                        {link.label}
                                    </Link>
                                ))}
                            </div>
                        </div>
                        <div className="flex items-center">
                            <div className="flex items-center gap-4">
                                <span className="text-sm text-gray-700 dark:text-gray-300">
                                    {isLibrarian && '👤 '}
                                    {user?.name}
                                </span>
                                <Link
                                    href={route('logout')}
                                    method="post"
                                    as="button"
                                    className="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300"
                                >
                                    Sair
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
                
                {/* Mobile menu */}
                <div className="sm:hidden border-t border-gray-200 dark:border-gray-700">
                    <div className="flex space-x-4 px-4 py-2 overflow-x-auto">
                        {navLinks.map((link) => (
                            <Link
                                key={link.href}
                                href={link.href}
                                className="text-sm font-medium text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 whitespace-nowrap"
                            >
                                {link.label}
                            </Link>
                        ))}
                    </div>
                </div>
            </nav>

            <FlashMessage />
            
            <main>
                {children}
            </main>
        </div>
    );
}