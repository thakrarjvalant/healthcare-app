// Minimal resilient API: tries backend then falls back to local mock data

const DEFAULT_TIMEOUT = 4000; // 4s

async function fetchWithTimeout(url, options = {}, timeout = DEFAULT_TIMEOUT) {
	// ...existing code...
	const controller = new AbortController();
	const id = setTimeout(() => controller.abort(), timeout);
	try {
		const res = await fetch(url, { ...options, signal: controller.signal });
		clearTimeout(id);
		if (!res.ok) throw new Error(`HTTP ${res.status}`);
		return res;
	} catch (e) {
		clearTimeout(id);
		throw e;
	}
}

// Mock/fallback data
const mock = {
	getCurrentUser: async () => {
		// Simulate async
		return {
			id: 'guest',
			name: 'Guest User',
			role: 'guest',
		};
	},
	getPatients: async () => {
		return [
			{ id: 'p1', name: 'Alice Doe', dob: '1980-01-01' },
			{ id: 'p2', name: 'Bob Roe', dob: '1990-06-15' },
		];
	},
	// ...add other mock endpoints as needed...
};

export async function getCurrentUser() {
	// Try backend first
	try {
		const res = await fetchWithTimeout('/api/currentUser', { method: 'GET' });
		const data = await res.json();
		return { data, fallback: false };
	} catch {
		const data = await mock.getCurrentUser();
		return { data, fallback: true };
	}
}

export async function getPatients() {
	try {
		const res = await fetchWithTimeout('/api/patients', { method: 'GET' });
		const data = await res.json();
		return { data, fallback: false };
	} catch {
		const data = await mock.getPatients();
		return { data, fallback: true };
	}
}