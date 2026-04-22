import React, { useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import AppLayout from '../../layouts/AppLayout';
import PrimaryButton from '../../Components/PrimaryButton';

export default function FeedbacksIndex({ feedbacks, filters, books }) {
    const [selectedRating, setSelectedRating] = useState(filters.rating || '');
    const [selectedBook, setSelectedBook] = useState(filters.book_id || '');

    const handleRatingChange = (e) => {
        setSelectedRating(e.target.value);
    };

    const handleBookChange = (e) => {
        setSelectedBook(e.target.value);
    };

    const applyFilters = () => {
        const params = new URLSearchParams();
        
        if (selectedRating) {
            params.append('rating', selectedRating);
        }
        if (selectedBook) {
            params.append('book_id', selectedBook);
        }

        router.get(route('feedbacks.index'), params.toString());
    };

    const resetFilters = () => {
        setSelectedRating('');
        setSelectedBook('');
        router.get(route('feedbacks.index'));
    };

    const getStars = (rating) => {
        return Array.from({ length: 5 }, (_, i) => (
            <span
                key={i}
                className={`text-lg ${
                    i < rating ? 'text-yellow-400' : 'text-gray-300 dark:text-gray-600'
                }`}
            >
                ★
            </span>
        ));
    };

    return (
        <AppLayout>
            <Head title="Avaliações de Livros" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="mb-8">
                        <h1 className="text-3xl font-bold text-gray-900 dark:text-white mb-6">
                            Avaliações de Livros
                        </h1>

                        {/* Filters */}
                        <div className="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-6">
                            <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Avaliação
                                    </label>
                                    <select
                                        value={selectedRating}
                                        onChange={handleRatingChange}
                                        className="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm"
                                    >
                                        <option value="">Todas as Avaliações</option>
                                        <option value={5}>⭐⭐⭐⭐⭐ 5 Estrelas</option>
                                        <option value={4}>⭐⭐⭐⭐ 4 Estrelas</option>
                                        <option value={3}>⭐⭐⭐ 3 Estrelas</option>
                                        <option value={2}>⭐⭐ 2 Estrelas</option>
                                        <option value={1}>⭐ 1 Estrela</option>
                                    </select>
                                </div>

                                <div>
                                    <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Livro
                                    </label>
                                    <select
                                        value={selectedBook}
                                        onChange={handleBookChange}
                                        className="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm"
                                    >
                                        <option value="">Todos os Livros</option>
                                        {books.map((book) => (
                                            <option key={book.value} value={book.value}>
                                                {book.label}
                                            </option>
                                        ))}
                                    </select>
                                </div>

                                <div className="flex items-end gap-2">
                                    <PrimaryButton onClick={applyFilters}>
                                        Aplicar Filtros
                                    </PrimaryButton>
                                    <button
                                        onClick={resetFilters}
                                        className="px-4 py-2 bg-gray-300 dark:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-400 dark:hover:bg-gray-500 transition duration-150"
                                    >
                                        Limpar
                                    </button>
                                </div>
                            </div>
                        </div>

                        {/* Stats */}
                        <div className="text-sm text-gray-600 dark:text-gray-400 mb-6">
                            {feedbacks.total} avaliações encontradas
                        </div>
                    </div>

                    {/* Feedbacks List */}
                    {feedbacks.data.length > 0 ? (
                        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            {feedbacks.data.map((feedback) => (
                                <div
                                    key={feedback.id}
                                    className="bg-white dark:bg-gray-800 rounded-lg shadow-md hover:shadow-lg transition-shadow duration-200"
                                >
                                    <div className="p-6">
                                        {/* Header */}
                                        <div className="flex justify-between items-start mb-3">
                                            <div>
                                                <Link
                                                    href={route('books.show', feedback.book.id)}
                                                    className="text-lg font-semibold text-gray-900 dark:text-white hover:text-indigo-600 dark:hover:text-indigo-400"
                                                >
                                                    {feedback.book.title}
                                                </Link>
                                                <p className="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                                    por {feedback.book.author}
                                                </p>
                                            </div>
                                            <Link
                                                href={route('feedbacks.edit', feedback.id)}
                                                className="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                                            >
                                                <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-4h-4v4m0 0h14m-4 0v4m0 0h-4" />
                                                </svg>
                                            </Link>
                                        </div>

                                        {/* Rating */}
                                        <div className="flex items-center mb-3">
                                            <div className="flex items-center mr-2">
                                                {getStars(feedback.rating)}
                                            </div>
                                            <span className="text-sm font-medium text-gray-600 dark:text-gray-400">
                                                {feedback.rating}/5
                                            </span>
                                        </div>

                                        {/* Reviewer */}
                                        <div className="flex items-center mb-3">
                                            <div className="w-8 h-8 bg-gray-300 dark:bg-gray-600 rounded-full flex items-center justify-center mr-3">
                                                <span className="text-sm text-gray-600 dark:text-gray-300">
                                                    {feedback.user.name.charAt(0).toUpperCase()}
                                                </span>
                                            </div>
                                            <div>
                                                <span className="text-sm font-medium text-gray-900 dark:text-white">
                                                    {feedback.user.name}
                                                </span>
                                                <span className="text-sm text-gray-600 dark:text-gray-400 ml-2">
                                                    {new Date(feedback.created_at).toLocaleDateString()}
                                                </span>
                                            </div>
                                        </div>

                                        {/* Comment */}
                                        {feedback.comment && (
                                            <div className="text-sm text-gray-600 dark:text-gray-400 leading-relaxed">
                                                <p className="line-clamp-4">
                                                    {feedback.comment}
                                                </p>
                                            </div>
                                        )}

                                        {/* Actions */}
                                        <div className="flex justify-end mt-4">
                                            <Link
                                                href={route('books.show', feedback.book.id)}
                                                className="text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-500 dark:hover:text-indigo-300"
                                            >
                                                Ver Livro
                                            </Link>
                                        </div>
                                    </div>
                                </div>
                            ))}
                        </div>
                    ) : (
                        <div className="text-center py-12">
                            <div className="bg-white dark:bg-gray-800 rounded-lg shadow p-8">
                                <h3 className="text-lg font-medium text-gray-900 dark:text-white mb-2">
                                    Nenhuma avaliação encontrada
                                </h3>
                                <p className="text-gray-600 dark:text-gray-400 mb-6">
                                    Tente ajustar seus filtros ou seja o primeiro a avaliar um livro!
                                </p>
                                <Link href={route('books.index')}>
                                    <PrimaryButton>
                                        Ver Livros
                                    </PrimaryButton>
                                </Link>
                            </div>
                        </div>
                    )}

                    {/* Pagination */}
                    {feedbacks.links && feedbacks.links.length > 3 && (
                        <div className="mt-8 flex justify-center">
                            <div className="flex gap-2">
                                {feedbacks.links.map((link, index) => (
                                    <Link
                                        key={index}
                                        href={link.url || '#'}
                                        className={`px-3 py-2 rounded-md text-sm ${
                                            link.active
                                                ? 'bg-indigo-600 text-white'
                                                : link.url
                                                ? 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'
                                                : 'bg-gray-100 dark:bg-gray-700 text-gray-400 cursor-not-allowed'
                                        }`}
                                        dangerouslySetInnerHTML={{ __html: link.label }}
                                    />
                                ))}
                            </div>
                        </div>
                    )}
                </div>
            </div>
        </AppLayout>
    );
}
