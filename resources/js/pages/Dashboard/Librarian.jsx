import React from 'react';
import { Head, Link } from '@inertiajs/react';
import AppLayout from '../../layouts/AppLayout';
import PrimaryButton from '../../Components/PrimaryButton';

export default function LibrarianDashboard({ stats, recentBooks, recentLoans, overdueLoans, popularBooks }) {
    return (
        <AppLayout>
            <Head title="Painel do Bibliotecário" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="mb-8">
                        <h1 className="text-3xl font-bold text-gray-900 dark:text-white mb-2">
                            Painel do Bibliotecário
                        </h1>
                        <p className="text-gray-600 dark:text-gray-400">
                            Gerencie a biblioteca e visualize as estatísticas do sistema
                        </p>
                    </div>

                    {/* Stats Cards */}
                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                        <div className="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                            <div className="flex items-center">
                                <div className="flex-shrink-0">
                                    <div className="w-8 h-8 bg-blue-500 rounded-md flex items-center justify-center">
                                        <span className="text-white text-sm font-medium">📚</span>
                                    </div>
                                </div>
                                <div className="ml-4">
                                    <p className="text-sm font-medium text-gray-600 dark:text-gray-400">Total de Livros</p>
                                    <p className="text-2xl font-bold text-gray-900 dark:text-white">{stats.total_books}</p>
                                </div>
                            </div>
                        </div>

                        <div className="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                            <div className="flex items-center">
                                <div className="flex-shrink-0">
                                    <div className="w-8 h-8 bg-green-500 rounded-md flex items-center justify-center">
                                        <span className="text-white text-sm font-medium">👥</span>
                                    </div>
                                </div>
                                <div className="ml-4">
                                    <p className="text-sm font-medium text-gray-600 dark:text-gray-400">Total de Usuários</p>
                                    <p className="text-2xl font-bold text-gray-900 dark:text-white">{stats.total_users}</p>
                                </div>
                            </div>
                        </div>

                        <div className="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                            <div className="flex items-center">
                                <div className="flex-shrink-0">
                                    <div className="w-8 h-8 bg-yellow-500 rounded-md flex items-center justify-center">
                                        <span className="text-white text-sm font-medium">📖</span>
                                    </div>
                                </div>
                                <div className="ml-4">
                                    <p className="text-sm font-medium text-gray-600 dark:text-gray-400">Empréstimos Ativos</p>
                                    <p className="text-2xl font-bold text-gray-900 dark:text-white">{stats.active_loans}</p>
                                </div>
                            </div>
                        </div>

                        <div className="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                            <div className="flex items-center">
                                <div className="flex-shrink-0">
                                    <div className="w-8 h-8 bg-red-500 rounded-md flex items-center justify-center">
                                        <span className="text-white text-sm font-medium">⚠️</span>
                                    </div>
                                </div>
                                <div className="ml-4">
                                    <p className="text-sm font-medium text-gray-600 dark:text-gray-400">Empréstimos Atrasados</p>
                                    <p className="text-2xl font-bold text-gray-900 dark:text-white">{stats.overdue_loans}</p>
                                </div>
                            </div>
                        </div>

                        <div className="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                            <div className="flex items-center">
                                <div className="flex-shrink-0">
                                    <div className="w-8 h-8 bg-purple-500 rounded-md flex items-center justify-center">
                                        <span className="text-white text-sm font-medium">⭐</span>
                                    </div>
                                </div>
                                <div className="ml-4">
                                    <p className="text-sm font-medium text-gray-600 dark:text-gray-400">Total de Avaliações</p>
                                    <p className="text-2xl font-bold text-gray-900 dark:text-white">{stats.total_feedbacks}</p>
                                </div>
                            </div>
                        </div>

                        <div className="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                            <div className="flex items-center">
                                <div className="flex-shrink-0">
                                    <div className="w-8 h-8 bg-indigo-500 rounded-md flex items-center justify-center">
                                        <span className="text-white text-sm font-medium">📊</span>
                                    </div>
                                </div>
                                <div className="ml-4">
                                    <p className="text-sm font-medium text-gray-600 dark:text-gray-400">Avaliação Média</p>
                                    <p className="text-2xl font-bold text-gray-900 dark:text-white">{stats.average_rating}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div className="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        {/* Recent Books */}
                        <div className="bg-white dark:bg-gray-800 rounded-lg shadow">
                            <div className="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                                <h2 className="text-lg font-semibold text-gray-900 dark:text-white">
                                    Livros Recentes
                                </h2>
                            </div>
                            <div className="p-6">
                                <div className="space-y-4">
                                    {recentBooks.map((book) => (
                                        <div key={book.id} className="flex items-center justify-between">
                                            <div>
                                                <Link
                                                    href={route('books.show', book.id)}
                                                    className="text-sm font-medium text-indigo-600 dark:text-indigo-400 hover:text-indigo-500"
                                                >
                                                    {book.title}
                                                </Link>
                                                <p className="text-sm text-gray-600 dark:text-gray-400">
                                                    por {book.author}
                                                </p>
                                            </div>
                                            <span className="text-sm text-gray-500 dark:text-gray-400">
                                                {book.year}
                                            </span>
                                        </div>
                                    ))}
                                </div>
                                <div className="mt-4">
                                    <Link href={route('books.index')}>
                                        <PrimaryButton className="text-sm">
                                            Ver Todos os Livros
                                        </PrimaryButton>
                                    </Link>
                                </div>
                            </div>
                        </div>

                        {/* Popular Books */}
                        <div className="bg-white dark:bg-gray-800 rounded-lg shadow">
                            <div className="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                                <h2 className="text-lg font-semibold text-gray-900 dark:text-white">
                                    Livros Populares
                                </h2>
                            </div>
                            <div className="p-6">
                                <div className="space-y-4">
                                    {popularBooks.map((book) => (
                                        <div key={book.id} className="flex items-center justify-between">
                                            <div>
                                                <Link
                                                    href={route('books.show', book.id)}
                                                    className="text-sm font-medium text-indigo-600 dark:text-indigo-400 hover:text-indigo-500"
                                                >
                                                    {book.title}
                                                </Link>
                                                <p className="text-sm text-gray-600 dark:text-gray-400">
                                                    {book.loans_count} empréstimo(s) ativo(s)
                                                </p>
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* Overdue Loans Alert */}
                    {overdueLoans.length > 0 && (
                        <div className="mt-8 bg-red-50 dark:bg-red-900/20 rounded-lg shadow p-6">
                            <div className="flex items-center mb-4">
                                <span className="text-red-600 dark:text-red-400 text-xl mr-2">⚠️</span>
                                <h2 className="text-lg font-semibold text-red-900 dark:text-red-100">
                                    Empréstimos Atrasados
                                </h2>
                            </div>
                            <div className="space-y-3">
                                {overdueLoans.slice(0, 5).map((loan) => (
                                    <div key={loan.id} className="flex items-center justify-between text-sm">
                                        <div>
                                            <span className="font-medium text-red-900 dark:text-red-100">
                                                {loan.user.name}
                                            </span>
                                            <span className="text-red-700 dark:text-red-300 ml-2">
                                                - {loan.book.title}
                                            </span>
                                        </div>
                                        <span className="text-red-600 dark:text-red-400">
                                            Devolução: {new Date(loan.due_date).toLocaleDateString()}
                                        </span>
                                    </div>
                                ))}
                            </div>
                            <div className="mt-4">
                                <Link href={route('loans.index')}>
                                    <PrimaryButton className="bg-red-600 hover:bg-red-700">
                                        Gerenciar Empréstimos
                                    </PrimaryButton>
                                </Link>
                            </div>
                        </div>
                    )}
                </div>
            </div>
        </AppLayout>
    );
}
