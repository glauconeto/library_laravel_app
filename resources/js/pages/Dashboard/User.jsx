import React from 'react';
import { Head, Link } from '@inertiajs/react';
import AppLayout from '../../layouts/AppLayout';
import PrimaryButton from '../../Components/PrimaryButton';

export default function UserDashboard({ stats, activeLoans, recentFeedbacks, availableBooks }) {
    return (
        <AppLayout>
            <Head title="Meu Painel" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="mb-8">
                        <h1 className="text-3xl font-bold text-gray-900 dark:text-white mb-2">
                            Meu Painel
                        </h1>
                        <p className="text-gray-600 dark:text-gray-400">
                            Gerencie seus empréstimos e descubra novos livros
                        </p>
                    </div>

                    {/* User Stats Cards */}
                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                        <div className="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                            <div className="flex items-center">
                                <div className="flex-shrink-0">
                                    <div className="w-8 h-8 bg-green-500 rounded-md flex items-center justify-center">
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
                                    <div className="w-8 h-8 bg-blue-500 rounded-md flex items-center justify-center">
                                        <span className="text-white text-sm font-medium">📚</span>
                                    </div>
                                </div>
                                <div className="ml-4">
                                    <p className="text-sm font-medium text-gray-600 dark:text-gray-400">Total de Empréstimos</p>
                                    <p className="text-2xl font-bold text-gray-900 dark:text-white">{stats.total_loans}</p>
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
                                    <p className="text-sm font-medium text-gray-600 dark:text-gray-400">Avaliações Dadas</p>
                                    <p className="text-2xl font-bold text-gray-900 dark:text-white">{stats.feedbacks_given}</p>
                                </div>
                            </div>
                        </div>

                        <div className="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                            <div className="flex items-center">
                                <div className="flex-shrink-0">
                                    <div className={`w-8 h-8 rounded-md flex items-center justify-center ${
                                        stats.can_borrow ? 'bg-green-500' : 'bg-red-500'
                                    }`}>
                                        <span className="text-white text-sm font-medium">
                                            {stats.can_borrow ? '✓' : '✗'}
                                        </span>
                                    </div>
                                </div>
                                <div className="ml-4">
                                    <p className="text-sm font-medium text-gray-600 dark:text-gray-400">Pode Emprestar</p>
                                    <p className="text-lg font-bold text-gray-900 dark:text-white">
                                        {stats.can_borrow ? 'Sim' : 'Não'}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
                        {/* Active Loans */}
                        <div className="lg:col-span-2">
                            <div className="bg-white dark:bg-gray-800 rounded-lg shadow">
                                <div className="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                                    <h2 className="text-lg font-semibold text-gray-900 dark:text-white">
                                        Meus Empréstimos Ativos
                                    </h2>
                                </div>
                                <div className="p-6">
                                    {activeLoans.length > 0 ? (
                                        <div className="space-y-4">
                                            {activeLoans.map((loan) => (
                                                <div key={loan.id} className="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                                    <div className="flex items-center">
                                                        <div className="w-12 h-16 bg-gray-200 dark:bg-gray-600 rounded flex items-center justify-center mr-4">
                                                            <span className="text-gray-500 dark:text-gray-400 text-xl">📚</span>
                                                        </div>
                                                        <div>
                                                            <Link
                                                                href={route('books.show', loan.book.id)}
                                                                className="font-medium text-gray-900 dark:text-white hover:text-indigo-600 dark:hover:text-indigo-400"
                                                            >
                                                                {loan.book.title}
                                                            </Link>
                                                            <p className="text-sm text-gray-600 dark:text-gray-400">
                                                                por {loan.book.author}
                                                            </p>
                                                            <p className="text-sm text-gray-500 dark:text-gray-400">
                                                                Devolução: {new Date(loan.due_date).toLocaleDateString()}
                                                            </p>
                                                        </div>
                                                    </div>
                                                    <div className="text-right">
                                                        <span className={`inline-flex px-2 py-1 text-xs font-semibold rounded-full ${
                                                            loan.is_overdue
                                                                ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'
                                                                : 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
                                                        }`}>
                                                            {loan.is_overdue ? 'Atrasado' : 'Ativo'}
                                                        </span>
                                                    </div>
                                                </div>
                                            ))}
                                            <div className="mt-4">
                                                <Link href={route('loans.index')}>
                                                    <PrimaryButton>
                                                        Ver Todos os Empréstimos
                                                    </PrimaryButton>
                                                </Link>
                                            </div>
                                        </div>
                                    ) : (
                                        <div className="text-center py-8">
                                            <div className="w-16 h-16 bg-gray-200 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-4">
                                                <span className="text-gray-500 dark:text-gray-400 text-2xl">📚</span>
                                            </div>
                                            <h3 className="text-lg font-medium text-gray-900 dark:text-white mb-2">
                                                Nenhum empréstimo ativo
                                            </h3>
                                            <p className="text-gray-600 dark:text-gray-400 mb-4">
                                                Você não tem livros emprestados no momento.
                                            </p>
                                            <Link href={route('books.index')}>
                                                <PrimaryButton>
                                                    Explorar Livros
                                                </PrimaryButton>
                                            </Link>
                                        </div>
                                    )}
                                </div>
                            </div>
                        </div>

                        {/* Recent Feedbacks */}
                        <div className="lg:col-span-1">
                            <div className="bg-white dark:bg-gray-800 rounded-lg shadow">
                                <div className="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                                    <h2 className="text-lg font-semibold text-gray-900 dark:text-white">
                                        Minhas Avaliações
                                    </h2>
                                </div>
                                <div className="p-6">
                                    {recentFeedbacks.length > 0 ? (
                                        <div className="space-y-4">
                                            {recentFeedbacks.map((feedback) => (
                                                <div key={feedback.id} className="border-b border-gray-200 dark:border-gray-700 pb-3 last:border-0">
                                                    <Link
                                                        href={route('books.show', feedback.book.id)}
                                                        className="text-sm font-medium text-gray-900 dark:text-white hover:text-indigo-600 dark:hover:text-indigo-400"
                                                    >
                                                        {feedback.book.title}
                                                    </Link>
                                                    <div className="flex items-center mt-1">
                                                        {[1, 2, 3, 4, 5].map((star) => (
                                                            <span
                                                                key={star}
                                                                className={`text-sm ${
                                                                    star <= feedback.rating
                                                                        ? 'text-yellow-400'
                                                                        : 'text-gray-300 dark:text-gray-600'
                                                                }`}
                                                            >
                                                                ★
                                                            </span>
                                                        ))}
                                                    </div>
                                                    {feedback.comment && (
                                                        <p className="text-sm text-gray-600 dark:text-gray-400 mt-2 line-clamp-2">
                                                            {feedback.comment}
                                                        </p>
                                                    )}
                                                </div>
                                            ))}
                                        </div>
                                    ) : (
                                        <div className="text-center py-4">
                                            <p className="text-sm text-gray-600 dark:text-gray-400">
                                                Você ainda não avaliou nenhum livro.
                                            </p>
                                        </div>
                                    )}
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* Available Books Recommendations */}
                    <div className="mt-8">
                        <div className="bg-white dark:bg-gray-800 rounded-lg shadow">
                            <div className="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                                <h2 className="text-lg font-semibold text-gray-900 dark:text-white">
                                    Recomendações para Você
                                </h2>
                                <p className="text-sm text-gray-600 dark:text-gray-400">
                                    Livros disponíveis que podem interessar você
                                </p>
                            </div>
                            <div className="p-6">
                                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                    {availableBooks.map((book) => (
                                        <div key={book.id} className="border border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:shadow-md transition-shadow">
                                            <div className="flex items-start justify-between mb-2">
                                                <h3 className="font-medium text-gray-900 dark:text-white text-sm">
                                                    {book.title}
                                                </h3>
                                                <span className="text-xs text-gray-500 dark:text-gray-400">
                                                    {book.year}
                                                </span>
                                            </div>
                                            <p className="text-sm text-gray-600 dark:text-gray-400 mb-2">
                                                por {book.author}
                                            </p>
                                            <p className="text-xs text-gray-500 dark:text-gray-400 mb-3">
                                                {book.genre}
                                            </p>
                                            <Link href={route('books.show', book.id)}>
                                                <PrimaryButton className="w-full text-sm">
                                                    Ver Detalhes
                                                </PrimaryButton>
                                            </Link>
                                        </div>
                                    ))}
                                </div>
                                <div className="mt-6 text-center">
                                    <Link href={route('books.index')}>
                                        <PrimaryButton>
                                            Ver Todos os Livros Disponíveis
                                        </PrimaryButton>
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
