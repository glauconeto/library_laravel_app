export interface User {
    id: number;
    name: string;
    email: string;
    roles: { name: string }[];
}

export interface Book {
    id: number;
    title: string;
    author: string;
    genre: string;
    year: number;
    stock: number;
    loans_count: number;
    description?: string;
}

export interface Genre {
    id: number;
    name: string;
}

export interface Loan {
    id: number;
    user_id: number;
    book_id: number;
    borrowed_at: string;
    returned_at: string | null;
    due_date: string;
    book: Book;
    user: User;
}

export interface Feedback {
    id: number;
    user_id: number;
    book_id: number;
    rating: number;
    comment: string | null;
    created_at: string;
    user: User;
    book: Book;
}

export interface PaginatedData<T> {
    data: T[];
    links: { url: string | null; label: string; active: boolean }[];
    total: number;
    current_page: number;
    last_page: number;
    per_page: number;
    from: number | null;
    to: number | null;
}

export interface PageProps {
    auth: {
        user: User;
    };
    flash: {
        success?: string;
        error?: string;
    };
    errors: Record<string, string>;
}