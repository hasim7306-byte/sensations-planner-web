import React, { useState, useEffect, createContext, useContext } from 'react';
import { BrowserRouter, Routes, Route, Navigate, Link, useNavigate } from 'react-router-dom';
import axios from 'axios';
import './App.css';

const BACKEND_URL = process.env.REACT_APP_BACKEND_URL;
const API = `${BACKEND_URL}/api`;

// Auth Context
const AuthContext = createContext();

const AuthProvider = ({ children }) => {
  const [user, setUser] = useState(null);
  const [token, setToken] = useState(localStorage.getItem('token'));
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    if (token) {
      axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
      fetchUserProfile();
    } else {
      setLoading(false);
    }
  }, [token]);

  const fetchUserProfile = async () => {
    try {
      const response = await axios.get(`${API}/users/me`);
      setUser(response.data);
    } catch (error) {
      logout();
    } finally {
      setLoading(false);
    }
  };

  const login = async (email, password) => {
    try {
      const response = await axios.post(`${API}/auth/login`, { email, password });
      const { access_token, user: userData } = response.data;
      
      localStorage.setItem('token', access_token);
      setToken(access_token);
      setUser(userData);
      axios.defaults.headers.common['Authorization'] = `Bearer ${access_token}`;
      
      return { success: true };
    } catch (error) {
      return { success: false, error: error.response?.data?.detail || 'Login failed' };
    }
  };

  const logout = () => {
    localStorage.removeItem('token');
    setToken(null);
    setUser(null);
    delete axios.defaults.headers.common['Authorization'];
  };

  return (
    <AuthContext.Provider value={{ user, token, login, logout, loading }}>
      {children}
    </AuthContext.Provider>
  );
};

const useAuth = () => {
  const context = useContext(AuthContext);
  if (!context) {
    throw new Error('useAuth must be used within AuthProvider');
  }
  return context;
};

// Components
const Navbar = () => {
  const { user, logout } = useAuth();
  const navigate = useNavigate();
  const [mobileMenuOpen, setMobileMenuOpen] = useState(false);

  const handleLogout = () => {
    logout();
    navigate('/login');
  };

  const navigationItems = [
    { name: 'Dashboard', path: '/dashboard', icon: '🏠' },
    { name: 'Roosters', path: '/schedule', icon: '📅' },
    { name: 'Tijd Registratie', path: '/time-tracking', icon: '⏰' },
    { name: 'Verlof', path: '/leave', icon: '🏖️' },
    { name: 'Chat', path: '/chat', icon: '💬' }
  ];

  if (user?.role === 'admin' || user?.role === 'manager') {
    navigationItems.push({ name: 'Medewerkers', path: '/employees', icon: '👥' });
  }

  return (
    <nav className="bg-blue-600 text-white shadow-lg">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="flex justify-between h-16">
          <div className="flex items-center">
            <Link to="/dashboard" className="text-xl font-bold flex-shrink-0">
              Senations To Go
            </Link>
            
            {/* Desktop Navigation */}
            <div className="hidden md:flex ml-8 space-x-4">
              {navigationItems.map((item) => (
                <Link
                  key={item.path}
                  to={item.path}
                  className="hover:bg-blue-700 px-3 py-2 rounded transition-colors"
                >
                  <span className="hidden lg:inline">{item.icon} </span>
                  {item.name}
                </Link>
              ))}
            </div>
          </div>

          {/* Desktop User Menu */}
          <div className="hidden md:flex items-center space-x-4">
            <span className="text-sm truncate max-w-32">
              {user?.name} ({user?.role})
            </span>
            <button
              onClick={handleLogout}
              className="bg-blue-700 hover:bg-blue-800 px-3 py-2 rounded text-sm transition-colors"
            >
              Uitloggen
            </button>
          </div>

          {/* Mobile menu button */}
          <div className="md:hidden flex items-center">
            <button
              onClick={() => setMobileMenuOpen(!mobileMenuOpen)}
              className="inline-flex items-center justify-center p-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-white"
              aria-expanded="false"
            >
              <span className="sr-only">Open main menu</span>
              {mobileMenuOpen ? (
                <svg className="block h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                </svg>
              ) : (
                <svg className="block h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 6h16M4 12h16M4 18h16" />
                </svg>
              )}
            </button>
          </div>
        </div>
      </div>

      {/* Mobile Navigation Menu */}
      {mobileMenuOpen && (
        <div className="md:hidden">
          <div className="px-2 pt-2 pb-3 space-y-1 bg-blue-700">
            {navigationItems.map((item) => (
              <Link
                key={item.path}
                to={item.path}
                onClick={() => setMobileMenuOpen(false)}
                className="block px-3 py-2 rounded-md text-base font-medium hover:bg-blue-800 transition-colors"
              >
                {item.icon} {item.name}
              </Link>
            ))}
            <div className="border-t border-blue-600 pt-4">
              <div className="px-3 py-2 text-sm text-blue-100">
                {user?.name} ({user?.role})
              </div>
              <button
                onClick={handleLogout}
                className="block w-full text-left px-3 py-2 rounded-md text-base font-medium hover:bg-blue-800 transition-colors"
              >
                🚪 Uitloggen
              </button>
            </div>
          </div>
        </div>
      )}
    </nav>
  );
};

const LoginPage = () => {
  const [formData, setFormData] = useState({
    email: '',
    password: ''
  });
  const [error, setError] = useState('');
  const [loading, setLoading] = useState(false);
  const { login } = useAuth();
  const navigate = useNavigate();

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true);
    setError('');

    const result = await login(formData.email, formData.password);

    if (result.success) {
      navigate('/dashboard');
    } else {
      setError(result.error);
    }
    setLoading(false);
  };

  const handleChange = (e) => {
    setFormData({ ...formData, [e.target.name]: e.target.value });
  };

  return (
    <div className="min-h-screen bg-gray-50 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
      <div className="max-w-md w-full space-y-8">
        <div>
          <h2 className="mt-6 text-center text-3xl font-extrabold text-gray-900">
            Senations To Go Planner
          </h2>
          <p className="mt-2 text-center text-sm text-gray-600">
            Inloggen op uw account
          </p>
        </div>
        <form className="mt-8 space-y-6" onSubmit={handleSubmit}>
          {error && (
            <div className="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
              {error}
            </div>
          )}
          
          <div className="space-y-4">
            <input
              name="email"
              type="email"
              required
              className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              placeholder="E-mailadres"
              value={formData.email}
              onChange={handleChange}
            />
            
            <input
              name="password"
              type="password"
              required
              className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              placeholder="Wachtwoord"
              value={formData.password}
              onChange={handleChange}
            />
          </div>

          <button
            type="submit"
            disabled={loading}
            className="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
          >
            {loading ? 'Bezig...' : 'Inloggen'}
          </button>

          <div className="text-center text-xs text-gray-500">
            Contact uw administrator voor toegang
          </div>
        </form>
      </div>
    </div>
  );
};

const Dashboard = () => {
  const [stats, setStats] = useState({});
  const [loading, setLoading] = useState(true);
  const { user } = useAuth();

  useEffect(() => {
    fetchDashboardStats();
  }, []);

  const fetchDashboardStats = async () => {
    try {
      if (user?.role === 'admin' || user?.role === 'manager') {
        const response = await axios.get(`${API}/dashboard/stats`);
        setStats(response.data);
      }
    } catch (error) {
      console.error('Error fetching dashboard stats:', error);
    } finally {
      setLoading(false);
    }
  };

  if (loading) {
    return <div className="flex justify-center items-center min-h-screen">Laden...</div>;
  }

  return (
    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <h1 className="text-3xl font-bold text-gray-900 mb-8">Dashboard</h1>
      
      <div className="mb-8">
        <h2 className="text-xl font-semibold mb-4">Welkom, {user?.name}!</h2>
        <p className="text-gray-600">Uw rol: {user?.role}</p>
      </div>

      {(user?.role === 'admin' || user?.role === 'manager') && (
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
          <div className="bg-white overflow-hidden shadow rounded-lg">
            <div className="p-5">
              <div className="flex items-center">
                <div className="flex-shrink-0">
                  <div className="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center">
                    <span className="text-white text-sm font-bold">👥</span>
                  </div>
                </div>
                <div className="ml-5 w-0 flex-1">
                  <dl>
                    <dt className="text-sm font-medium text-gray-500 truncate">Totaal Medewerkers</dt>
                    <dd className="text-lg font-medium text-gray-900">{stats.total_employees || 0}</dd>
                  </dl>
                </div>
              </div>
            </div>
          </div>

          <div className="bg-white overflow-hidden shadow rounded-lg">
            <div className="p-5">
              <div className="flex items-center">
                <div className="flex-shrink-0">
                  <div className="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
                    <span className="text-white text-sm font-bold">📅</span>
                  </div>
                </div>
                <div className="ml-5 w-0 flex-1">
                  <dl>
                    <dt className="text-sm font-medium text-gray-500 truncate">Diensten Vandaag</dt>
                    <dd className="text-lg font-medium text-gray-900">{stats.shifts_today || 0}</dd>
                  </dl>
                </div>
              </div>
            </div>
          </div>

          <div className="bg-white overflow-hidden shadow rounded-lg">
            <div className="p-5">
              <div className="flex items-center">
                <div className="flex-shrink-0">
                  <div className="w-8 h-8 bg-yellow-500 rounded-full flex items-center justify-center">
                    <span className="text-white text-sm font-bold">⏰</span>
                  </div>
                </div>
                <div className="ml-5 w-0 flex-1">
                  <dl>
                    <dt className="text-sm font-medium text-gray-500 truncate">Actief Ingeklokt</dt>
                    <dd className="text-lg font-medium text-gray-900">{stats.active_employees || 0}</dd>
                  </dl>
                </div>
              </div>
            </div>
          </div>

          <div className="bg-white overflow-hidden shadow rounded-lg">
            <div className="p-5">
              <div className="flex items-center">
                <div className="flex-shrink-0">
                  <div className="w-8 h-8 bg-red-500 rounded-full flex items-center justify-center">
                    <span className="text-white text-sm font-bold">📝</span>
                  </div>
                </div>
                <div className="ml-5 w-0 flex-1">
                  <dl>
                    <dt className="text-sm font-medium text-gray-500 truncate">Verlof Aanvragen</dt>
                    <dd className="text-lg font-medium text-gray-900">{stats.pending_leaves || 0}</dd>
                  </dl>
                </div>
              </div>
            </div>
          </div>
        </div>
      )}

      <div className="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <div className="bg-white shadow rounded-lg p-6">
          <h3 className="text-lg font-medium text-gray-900 mb-4">Snelle Acties</h3>
          <div className="space-y-3">
            <Link
              to="/time-tracking"
              className="block w-full bg-blue-50 hover:bg-blue-100 border border-blue-200 rounded-lg p-4 text-left transition-colors"
            >
              <div className="flex items-center">
                <span className="text-2xl mr-3">⏰</span>
                <div>
                  <h4 className="font-medium text-gray-900">In/Uitklokken</h4>
                  <p className="text-sm text-gray-600">Registreer uw werktijd</p>
                </div>
              </div>
            </Link>
            
            <Link
              to="/leave"
              className="block w-full bg-green-50 hover:bg-green-100 border border-green-200 rounded-lg p-4 text-left transition-colors"
            >
              <div className="flex items-center">
                <span className="text-2xl mr-3">🏖️</span>
                <div>
                  <h4 className="font-medium text-gray-900">Verlof Aanvragen</h4>
                  <p className="text-sm text-gray-600">Vraag verlof aan</p>
                </div>
              </div>
            </Link>
            
            <Link
              to="/schedule"
              className="block w-full bg-purple-50 hover:bg-purple-100 border border-purple-200 rounded-lg p-4 text-left transition-colors"
            >
              <div className="flex items-center">
                <span className="text-2xl mr-3">📅</span>
                <div>
                  <h4 className="font-medium text-gray-900">Rooster Bekijken</h4>
                  <p className="text-sm text-gray-600">Bekijk uw werkrooster</p>
                </div>
              </div>
            </Link>
          </div>
        </div>

        <div className="bg-white shadow rounded-lg p-6">
          <h3 className="text-lg font-medium text-gray-900 mb-4">Recente Activiteit</h3>
          <div className="space-y-3">
            <div className="flex items-center text-sm text-gray-600">
              <span className="w-2 h-2 bg-green-400 rounded-full mr-3"></span>
              Systeem gestart en gereed voor gebruik
            </div>
            <div className="flex items-center text-sm text-gray-600">
              <span className="w-2 h-2 bg-blue-400 rounded-full mr-3"></span>
              Account aangemaakt: {user?.name}
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};

const TimeTracking = () => {
  const [clockedIn, setClockedIn] = useState(false);
  const [currentEntry, setCurrentEntry] = useState(null);
  const [entries, setEntries] = useState([]);
  const [loading, setLoading] = useState(false);

  useEffect(() => {
    fetchTimeEntries();
  }, []);

  const fetchTimeEntries = async () => {
    try {
      const response = await axios.get(`${API}/time/entries`);
      setEntries(response.data);
      
      // Check if currently clocked in
      const today = new Date().toISOString().split('T')[0];
      const todayEntry = response.data.find(entry => 
        entry.date === today && entry.clock_in && !entry.clock_out
      );
      if (todayEntry) {
        setClockedIn(true);
        setCurrentEntry(todayEntry);
      }
    } catch (error) {
      console.error('Error fetching time entries:', error);
    }
  };

  const handleClockIn = async () => {
    setLoading(true);
    try {
      const response = await axios.post(`${API}/time/clock-in`);
      setClockedIn(true);
      fetchTimeEntries();
      alert('Succesvol ingeklokt!');
    } catch (error) {
      alert(error.response?.data?.detail || 'Fout bij inklokken');
    } finally {
      setLoading(false);
    }
  };

  const handleClockOut = async () => {
    setLoading(true);
    try {
      const response = await axios.post(`${API}/time/clock-out`);
      setClockedIn(false);
      setCurrentEntry(null);
      fetchTimeEntries();
      alert(`Succesvol uitgeklokt! Totaal: ${response.data.total_hours} uren`);
    } catch (error) {
      alert(error.response?.data?.detail || 'Fout bij uitklokken');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <h1 className="text-3xl font-bold text-gray-900 mb-8">Tijd Registratie</h1>
      
      <div className="bg-white shadow rounded-lg p-6 mb-8">
        <h2 className="text-xl font-semibold mb-4">In/Uitklokken</h2>
        <div className="flex items-center space-x-4">
          {!clockedIn ? (
            <button
              onClick={handleClockIn}
              disabled={loading}
              className="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-medium disabled:opacity-50"
            >
              {loading ? 'Bezig...' : '🕐 Inklokken'}
            </button>
          ) : (
            <button
              onClick={handleClockOut}
              disabled={loading}
              className="bg-red-600 hover:bg-red-700 text-white px-6 py-3 rounded-lg font-medium disabled:opacity-50"
            >
              {loading ? 'Bezig...' : '🕐 Uitklokken'}
            </button>
          )}
          
          {clockedIn && (
            <div className="bg-green-100 text-green-800 px-4 py-2 rounded-lg">
              <span className="font-medium">Status: Ingeklokt</span> 
              {currentEntry && (
                <span className="text-sm ml-2">
                  sinds {new Date(currentEntry.clock_in).toLocaleTimeString()}
                </span>
              )}
            </div>
          )}
        </div>
      </div>

      <div className="bg-white shadow rounded-lg p-6">
        <h2 className="text-xl font-semibold mb-4">Tijd Overzicht</h2>
        <div className="overflow-x-auto">
          <table className="min-w-full divide-y divide-gray-200">
            <thead className="bg-gray-50">
              <tr>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Datum</th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Inklokken</th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Uitklokken</th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Totaal Uren</th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
              </tr>
            </thead>
            <tbody className="bg-white divide-y divide-gray-200">
              {entries.map((entry) => (
                <tr key={entry.id}>
                  <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {new Date(entry.date).toLocaleDateString()}
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {entry.clock_in ? new Date(entry.clock_in).toLocaleTimeString() : '-'}
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {entry.clock_out ? new Date(entry.clock_out).toLocaleTimeString() : '-'}
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {entry.total_hours ? `${entry.total_hours}h` : '-'}
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap">
                    <span className={`inline-flex px-2 py-1 text-xs font-semibold rounded-full ${
                      entry.is_approved ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'
                    }`}>
                      {entry.is_approved ? 'Goedgekeurd' : 'In afwachting'}
                    </span>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  );
};

const SchedulePage = () => {
  const [shifts, setShifts] = useState([]);
  const [users, setUsers] = useState([]);
  const [newShift, setNewShift] = useState({
    employee_id: '',
    date: '',
    start_time: '',
    end_time: '',
    department: '',
    notes: ''
  });
  const [showForm, setShowForm] = useState(false);
  const { user } = useAuth();

  useEffect(() => {
    fetchShifts();
    if (user?.role === 'admin' || user?.role === 'manager') {
      fetchUsers();
    }
  }, [user]);

  const fetchShifts = async () => {
    try {
      const response = await axios.get(`${API}/shifts`);
      setShifts(response.data);
    } catch (error) {
      console.error('Error fetching shifts:', error);
    }
  };

  const fetchUsers = async () => {
    try {
      const response = await axios.get(`${API}/users`);
      setUsers(response.data.filter(u => u.role === 'employee'));
    } catch (error) {
      console.error('Error fetching users:', error);
    }
  };

  const handleCreateShift = async (e) => {
    e.preventDefault();
    try {
      await axios.post(`${API}/shifts`, newShift);
      setNewShift({
        employee_id: '',
        date: '',
        start_time: '',
        end_time: '',
        department: '',
        notes: ''
      });
      setShowForm(false);
      fetchShifts();
      alert('Dienst succesvol aangemaakt!');
    } catch (error) {
      alert(error.response?.data?.detail || 'Fout bij aanmaken dienst');
    }
  };

  return (
    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div className="flex justify-between items-center mb-8">
        <h1 className="text-3xl font-bold text-gray-900">Roosters</h1>
        {(user?.role === 'admin' || user?.role === 'manager') && (
          <button
            onClick={() => setShowForm(!showForm)}
            className="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg"
          >
            {showForm ? 'Annuleren' : 'Nieuwe Dienst'}
          </button>
        )}
      </div>

      {showForm && (
        <div className="bg-white shadow rounded-lg p-6 mb-8">
          <h2 className="text-xl font-semibold mb-4">Nieuwe Dienst Aanmaken</h2>
          <form onSubmit={handleCreateShift} className="grid grid-cols-1 md:grid-cols-2 gap-4">
            <select
              value={newShift.employee_id}
              onChange={(e) => setNewShift({...newShift, employee_id: e.target.value})}
              required
              className="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
              <option value="">Selecteer medewerker</option>
              {users.map(user => (
                <option key={user.id} value={user.id}>{user.name}</option>
              ))}
            </select>

            <input
              type="date"
              value={newShift.date}
              onChange={(e) => setNewShift({...newShift, date: e.target.value})}
              required
              className="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            />

            <input
              type="time"
              value={newShift.start_time}
              onChange={(e) => setNewShift({...newShift, start_time: e.target.value})}
              required
              placeholder="Start tijd"
              className="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            />

            <input
              type="time"
              value={newShift.end_time}
              onChange={(e) => setNewShift({...newShift, end_time: e.target.value})}
              required
              placeholder="Eind tijd"
              className="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            />

            <input
              type="text"
              value={newShift.department}
              onChange={(e) => setNewShift({...newShift, department: e.target.value})}
              placeholder="Afdeling"
              className="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            />

            <input
              type="text"
              value={newShift.notes}
              onChange={(e) => setNewShift({...newShift, notes: e.target.value})}
              placeholder="Opmerkingen"
              className="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            />

            <div className="md:col-span-2">
              <button
                type="submit"
                className="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg"
              >
                Dienst Aanmaken
              </button>
            </div>
          </form>
        </div>
      )}

      <div className="bg-white shadow rounded-lg p-6">
        <h2 className="text-xl font-semibold mb-4">Rooster Overzicht</h2>
        <div className="overflow-x-auto">
          <table className="min-w-full divide-y divide-gray-200">
            <thead className="bg-gray-50">
              <tr>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Medewerker</th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Datum</th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tijd</th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Afdeling</th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Opmerkingen</th>
              </tr>
            </thead>
            <tbody className="bg-white divide-y divide-gray-200">
              {shifts.map((shift) => {
                const employee = users.find(u => u.id === shift.employee_id);
                return (
                  <tr key={shift.id}>
                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                      {employee?.name || 'Onbekend'}
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                      {new Date(shift.date).toLocaleDateString()}
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                      {shift.start_time} - {shift.end_time}
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                      {shift.department || '-'}
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap">
                      <span className={`inline-flex px-2 py-1 text-xs font-semibold rounded-full ${
                        shift.status === 'confirmed' ? 'bg-green-100 text-green-800' :
                        shift.status === 'completed' ? 'bg-blue-100 text-blue-800' :
                        'bg-yellow-100 text-yellow-800'
                      }`}>
                        {shift.status === 'scheduled' ? 'Gepland' :
                         shift.status === 'confirmed' ? 'Bevestigd' :
                         shift.status === 'completed' ? 'Voltooid' : shift.status}
                      </span>
                    </td>
                    <td className="px-6 py-4 text-sm text-gray-900">
                      {shift.notes || '-'}
                    </td>
                  </tr>
                );
              })}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  );
};

const LeavePage = () => {
  const [leaveRequests, setLeaveRequests] = useState([]);
  const [newRequest, setNewRequest] = useState({
    leave_type: 'vacation',
    start_date: '',
    end_date: '',
    reason: ''
  });
  const [showForm, setShowForm] = useState(false);
  const { user } = useAuth();

  useEffect(() => {
    fetchLeaveRequests();
  }, []);

  const fetchLeaveRequests = async () => {
    try {
      const response = await axios.get(`${API}/leave/requests`);
      setLeaveRequests(response.data);
    } catch (error) {
      console.error('Error fetching leave requests:', error);
    }
  };

  const handleSubmitRequest = async (e) => {
    e.preventDefault();
    try {
      await axios.post(`${API}/leave/request`, newRequest);
      setNewRequest({
        leave_type: 'vacation',
        start_date: '',
        end_date: '',
        reason: ''
      });
      setShowForm(false);
      fetchLeaveRequests();
      alert('Verlofaanvraag succesvol ingediend!');
    } catch (error) {
      alert(error.response?.data?.detail || 'Fout bij indienen aanvraag');
    }
  };

  const handleApprove = async (requestId) => {
    try {
      await axios.put(`${API}/leave/requests/${requestId}/approve`);
      fetchLeaveRequests();
      alert('Verlofaanvraag goedgekeurd!');
    } catch (error) {
      alert(error.response?.data?.detail || 'Fout bij goedkeuren');
    }
  };

  const handleReject = async (requestId) => {
    try {
      await axios.put(`${API}/leave/requests/${requestId}/reject`);
      fetchLeaveRequests();
      alert('Verlofaanvraag afgewezen!');
    } catch (error) {
      alert(error.response?.data?.detail || 'Fout bij afwijzen');
    }
  };

  const getLeaveTypeText = (type) => {
    const types = {
      vacation: 'Vakantie',
      sick: 'Ziekte',
      personal: 'Persoonlijk',
      maternity: 'Zwangerschapsverlof',
      other: 'Anders'
    };
    return types[type] || type;
  };

  const getStatusText = (status) => {
    const statuses = {
      pending: 'In afwachting',
      approved: 'Goedgekeurd',
      rejected: 'Afgewezen'
    };
    return statuses[status] || status;
  };

  return (
    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div className="flex justify-between items-center mb-8">
        <h1 className="text-3xl font-bold text-gray-900">Verlofbeheer</h1>
        <button
          onClick={() => setShowForm(!showForm)}
          className="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg"
        >
          {showForm ? 'Annuleren' : 'Nieuwe Aanvraag'}
        </button>
      </div>

      {showForm && (
        <div className="bg-white shadow rounded-lg p-6 mb-8">
          <h2 className="text-xl font-semibold mb-4">Verlof Aanvragen</h2>
          <form onSubmit={handleSubmitRequest} className="space-y-4">
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <select
                value={newRequest.leave_type}
                onChange={(e) => setNewRequest({...newRequest, leave_type: e.target.value})}
                className="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
                <option value="vacation">Vakantie</option>
                <option value="sick">Ziekte</option>
                <option value="personal">Persoonlijk</option>
                <option value="maternity">Zwangerschapsverlof</option>
                <option value="other">Anders</option>
              </select>

              <div></div>

              <input
                type="date"
                value={newRequest.start_date}
                onChange={(e) => setNewRequest({...newRequest, start_date: e.target.value})}
                required
                className="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                placeholder="Start datum"
              />

              <input
                type="date"
                value={newRequest.end_date}
                onChange={(e) => setNewRequest({...newRequest, end_date: e.target.value})}
                required
                className="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                placeholder="Eind datum"
              />
            </div>

            <textarea
              value={newRequest.reason}
              onChange={(e) => setNewRequest({...newRequest, reason: e.target.value})}
              required
              rows={3}
              className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              placeholder="Reden voor verlof..."
            />

            <button
              type="submit"
              className="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg"
            >
              Aanvraag Indienen
            </button>
          </form>
        </div>
      )}

      <div className="bg-white shadow rounded-lg p-6">
        <h2 className="text-xl font-semibold mb-4">Verlof Overzicht</h2>
        <div className="overflow-x-auto">
          <table className="min-w-full divide-y divide-gray-200">
            <thead className="bg-gray-50">
              <tr>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Start Datum</th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Eind Datum</th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reden</th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                {(user?.role === 'admin' || user?.role === 'manager') && (
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acties</th>
                )}
              </tr>
            </thead>
            <tbody className="bg-white divide-y divide-gray-200">
              {leaveRequests.map((request) => (
                <tr key={request.id}>
                  <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {getLeaveTypeText(request.leave_type)}
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {new Date(request.start_date).toLocaleDateString()}
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {new Date(request.end_date).toLocaleDateString()}
                  </td>
                  <td className="px-6 py-4 text-sm text-gray-900 max-w-xs truncate">
                    {request.reason}
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap">
                    <span className={`inline-flex px-2 py-1 text-xs font-semibold rounded-full ${
                      request.status === 'approved' ? 'bg-green-100 text-green-800' :
                      request.status === 'rejected' ? 'bg-red-100 text-red-800' :
                      'bg-yellow-100 text-yellow-800'
                    }`}>
                      {getStatusText(request.status)}
                    </span>
                  </td>
                  {(user?.role === 'admin' || user?.role === 'manager') && request.status === 'pending' && (
                    <td className="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                      <button
                        onClick={() => handleApprove(request.id)}
                        className="text-green-600 hover:text-green-900"
                      >
                        Goedkeuren
                      </button>
                      <button
                        onClick={() => handleReject(request.id)}
                        className="text-red-600 hover:text-red-900"
                      >
                        Afwijzen
                      </button>
                    </td>
                  )}
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  );
};

const ChatPage = () => {
  const [messages, setMessages] = useState([]);
  const [newMessage, setNewMessage] = useState('');
  const [loading, setLoading] = useState(false);
  const { user } = useAuth();

  useEffect(() => {
    fetchMessages();
    const interval = setInterval(fetchMessages, 3000); // Poll every 3 seconds
    return () => clearInterval(interval);
  }, []);

  const fetchMessages = async () => {
    try {
      const response = await axios.get(`${API}/chat/messages`);
      setMessages(response.data);
    } catch (error) {
      console.error('Error fetching messages:', error);
    }
  };

  const handleSendMessage = async (e) => {
    e.preventDefault();
    if (!newMessage.trim()) return;

    setLoading(true);
    try {
      await axios.post(`${API}/chat/messages`, { message: newMessage });
      setNewMessage('');
      fetchMessages();
    } catch (error) {
      alert('Fout bij verzenden bericht');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <h1 className="text-3xl font-bold text-gray-900 mb-8">Team Chat</h1>
      
      <div className="bg-white shadow rounded-lg">
        <div className="h-96 overflow-y-auto p-6 border-b">
          <div className="space-y-4">
            {messages.map((message) => (
              <div
                key={message.id}
                className={`flex ${message.sender_id === user?.id ? 'justify-end' : 'justify-start'}`}
              >
                <div
                  className={`max-w-xs lg:max-w-md px-4 py-2 rounded-lg ${
                    message.sender_id === user?.id
                      ? 'bg-blue-600 text-white'
                      : 'bg-gray-100 text-gray-900'
                  }`}
                >
                  {message.sender_id !== user?.id && (
                    <p className="text-xs font-medium mb-1 opacity-75">
                      {message.sender_name}
                    </p>
                  )}
                  <p className="text-sm">{message.message}</p>
                  <p className={`text-xs mt-1 ${
                    message.sender_id === user?.id ? 'text-blue-100' : 'text-gray-500'
                  }`}>
                    {new Date(message.timestamp).toLocaleTimeString()}
                  </p>
                </div>
              </div>
            ))}
          </div>
        </div>
        
        <form onSubmit={handleSendMessage} className="p-6">
          <div className="flex space-x-4">
            <input
              type="text"
              value={newMessage}
              onChange={(e) => setNewMessage(e.target.value)}
              placeholder="Type een bericht..."
              className="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
            <button
              type="submit"
              disabled={loading || !newMessage.trim()}
              className="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg disabled:opacity-50"
            >
              {loading ? 'Bezig...' : 'Verzenden'}
            </button>
          </div>
        </form>
      </div>
    </div>
  );
};

const EmployeesPage = () => {
  const [employees, setEmployees] = useState([]);
  const [newUser, setNewUser] = useState({
    name: '',
    email: '',
    password: '',
    role: 'employee',
    department: '',
    phone: '',
    hourly_rate: ''
  });
  const [showForm, setShowForm] = useState(false);
  const [editingUser, setEditingUser] = useState(null);
  const [loading, setLoading] = useState(false);
  const { user } = useAuth();

  useEffect(() => {
    if (user?.role === 'admin' || user?.role === 'manager') {
      fetchEmployees();
    }
  }, [user]);

  const fetchEmployees = async () => {
    try {
      const response = await axios.get(`${API}/users`);
      setEmployees(response.data);
    } catch (error) {
      console.error('Error fetching employees:', error);
    }
  };

  const handleCreateUser = async (e) => {
    e.preventDefault();
    if (user?.role !== 'admin') {
      alert('Alleen admins kunnen gebruikers aanmaken');
      return;
    }

    setLoading(true);
    try {
      const userData = {
        ...newUser,
        hourly_rate: newUser.hourly_rate ? parseFloat(newUser.hourly_rate) : null
      };
      
      await axios.post(`${API}/users`, userData);
      
      setNewUser({
        name: '',
        email: '',
        password: '',
        role: 'employee',
        department: '',
        phone: '',
        hourly_rate: ''
      });
      setShowForm(false);
      fetchEmployees();
      alert('Gebruiker succesvol aangemaakt!');
    } catch (error) {
      alert(error.response?.data?.detail || 'Fout bij aanmaken gebruiker');
    } finally {
      setLoading(false);
    }
  };

  const handleUpdateUser = async (userId, updateData) => {
    if (user?.role !== 'admin') {
      alert('Alleen admins kunnen gebruikers bewerken');
      return;
    }

    try {
      await axios.put(`${API}/users/${userId}`, updateData);
      fetchEmployees();
      setEditingUser(null);
      alert('Gebruiker succesvol bijgewerkt!');
    } catch (error) {
      alert(error.response?.data?.detail || 'Fout bij bijwerken gebruiker');
    }
  };

  const handleDeleteUser = async (userId) => {
    if (user?.role !== 'admin') {
      alert('Alleen admins kunnen gebruikers verwijderen');
      return;
    }

    if (window.confirm('Weet u zeker dat u deze gebruiker permanent wilt verwijderen?')) {
      try {
        await axios.delete(`${API}/users/${userId}`);
        fetchEmployees();
        alert('Gebruiker verwijderd!');
      } catch (error) {
        alert(error.response?.data?.detail || 'Fout bij verwijderen gebruiker');
      }
    }
  };

  const getRoleText = (role) => {
    const roles = {
      admin: 'Administrator',
      manager: 'Manager',
      employee: 'Medewerker'
    };
    return roles[role] || role;
  };

  if (user?.role !== 'admin' && user?.role !== 'manager') {
    return (
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div className="text-center">
          <h1 className="text-2xl font-bold text-gray-900">Geen toegang</h1>
          <p className="text-gray-600">U heeft geen toegang tot deze pagina.</p>
        </div>
      </div>
    );
  }

  return (
    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div className="flex justify-between items-center mb-8">
        <h1 className="text-3xl font-bold text-gray-900">Medewerker Beheer</h1>
        {user?.role === 'admin' && (
          <button
            onClick={() => setShowForm(!showForm)}
            className="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg"
          >
            {showForm ? 'Annuleren' : '+ Nieuwe Gebruiker'}
          </button>
        )}
      </div>

      {showForm && user?.role === 'admin' && (
        <div className="bg-white shadow rounded-lg p-6 mb-8">
          <h2 className="text-xl font-semibold mb-4">Nieuwe Gebruiker Aanmaken</h2>
          <form onSubmit={handleCreateUser} className="grid grid-cols-1 md:grid-cols-2 gap-4">
            <input
              type="text"
              value={newUser.name}
              onChange={(e) => setNewUser({...newUser, name: e.target.value})}
              required
              placeholder="Volledige naam"
              className="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            />

            <input
              type="email"
              value={newUser.email}
              onChange={(e) => setNewUser({...newUser, email: e.target.value})}
              required
              placeholder="E-mailadres"
              className="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            />

            <input
              type="password"
              value={newUser.password}
              onChange={(e) => setNewUser({...newUser, password: e.target.value})}
              required
              placeholder="Wachtwoord"
              className="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            />

            <select
              value={newUser.role}
              onChange={(e) => setNewUser({...newUser, role: e.target.value})}
              className="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
              <option value="employee">Medewerker</option>
              <option value="manager">Manager</option>
              <option value="admin">Administrator</option>
            </select>

            <input
              type="text"
              value={newUser.department}
              onChange={(e) => setNewUser({...newUser, department: e.target.value})}
              placeholder="Afdeling"
              className="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            />

            <input
              type="tel"
              value={newUser.phone}
              onChange={(e) => setNewUser({...newUser, phone: e.target.value})}
              placeholder="Telefoonnummer"
              className="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            />

            <input
              type="number"
              step="0.01"
              value={newUser.hourly_rate}
              onChange={(e) => setNewUser({...newUser, hourly_rate: e.target.value})}
              placeholder="Uurloon (€)"
              className="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            />

            <div></div>

            <div className="md:col-span-2">
              <button
                type="submit"
                disabled={loading}
                className="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg disabled:opacity-50"
              >
                {loading ? 'Bezig...' : 'Gebruiker Aanmaken'}
              </button>
            </div>
          </form>
        </div>
      )}
      
      <div className="bg-white shadow rounded-lg p-6">
        <div className="overflow-x-auto">
          <table className="min-w-full divide-y divide-gray-200">
            <thead className="bg-gray-50">
              <tr>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Naam</th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rol</th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Afdeling</th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Telefoon</th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Uurloon</th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                {user?.role === 'admin' && (
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acties</th>
                )}
              </tr>
            </thead>
            <tbody className="bg-white divide-y divide-gray-200">
              {employees.map((employee) => (
                <tr key={employee.id}>
                  <td className="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                    {employee.name}
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {employee.email}
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    <span className={`inline-flex px-2 py-1 text-xs font-semibold rounded-full ${
                      employee.role === 'admin' ? 'bg-purple-100 text-purple-800' :
                      employee.role === 'manager' ? 'bg-blue-100 text-blue-800' :
                      'bg-green-100 text-green-800'
                    }`}>
                      {getRoleText(employee.role)}
                    </span>
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {employee.department || '-'}
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {employee.phone || '-'}
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {employee.hourly_rate ? `€${employee.hourly_rate}` : '-'}
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap">
                    <span className={`inline-flex px-2 py-1 text-xs font-semibold rounded-full ${
                      employee.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                    }`}>
                      {employee.is_active ? 'Actief' : 'Inactief'}
                    </span>
                  </td>
                  {user?.role === 'admin' && (
                    <td className="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                      <button
                        onClick={() => setEditingUser(employee)}
                        className="text-indigo-600 hover:text-indigo-900"
                      >
                        Bewerken
                      </button>
                      {employee.id !== user.id && (
                        <button
                          onClick={() => handleDeleteUser(employee.id)}
                          className="text-red-600 hover:text-red-900"
                        >
                          Verwijderen
                        </button>
                      )}
                    </td>
                  )}
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </div>

      {/* Edit User Modal */}
      {editingUser && user?.role === 'admin' && (
        <div className="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full flex items-center justify-center">
          <div className="bg-white p-8 rounded-lg shadow-xl max-w-md w-full mx-4">
            <h3 className="text-lg font-medium text-gray-900 mb-4">Gebruiker Bewerken</h3>
            <form onSubmit={(e) => {
              e.preventDefault();
              const formData = new FormData(e.target);
              const updateData = {
                name: formData.get('name'),
                email: formData.get('email'),
                role: formData.get('role'),
                department: formData.get('department'),
                phone: formData.get('phone'),
                hourly_rate: formData.get('hourly_rate') ? parseFloat(formData.get('hourly_rate')) : null
              };
              handleUpdateUser(editingUser.id, updateData);
            }} className="space-y-4">
              <input
                name="name"
                type="text"
                defaultValue={editingUser.name}
                required
                placeholder="Volledige naam"
                className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
              
              <input
                name="email"
                type="email"
                defaultValue={editingUser.email}
                required
                placeholder="E-mailadres"
                className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
              
              <select
                name="role"
                defaultValue={editingUser.role}
                className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
                <option value="employee">Medewerker</option>
                <option value="manager">Manager</option>
                <option value="admin">Administrator</option>
              </select>
              
              <input
                name="department"
                type="text"
                defaultValue={editingUser.department || ''}
                placeholder="Afdeling"
                className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
              
              <input
                name="phone"
                type="tel"
                defaultValue={editingUser.phone || ''}
                placeholder="Telefoonnummer"
                className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
              
              <input
                name="hourly_rate"
                type="number"
                step="0.01"
                defaultValue={editingUser.hourly_rate || ''}
                placeholder="Uurloon (€)"
                className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
              
              <div className="flex justify-end space-x-3 pt-4">
                <button
                  type="button"
                  onClick={() => setEditingUser(null)}
                  className="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50"
                >
                  Annuleren
                </button>
                <button
                  type="submit"
                  className="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
                >
                  Opslaan
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
};

// Protected Route Component
const ProtectedRoute = ({ children }) => {
  const { user, loading } = useAuth();

  if (loading) {
    return (
      <div className="min-h-screen flex items-center justify-center">
        <div className="text-center">
          <div className="animate-spin rounded-full h-32 w-32 border-b-2 border-blue-600 mx-auto"></div>
          <p className="mt-4 text-gray-600">Laden...</p>
        </div>
      </div>
    );
  }

  if (!user) {
    return <Navigate to="/login" replace />;
  }

  return (
    <div className="min-h-screen bg-gray-50">
      <Navbar />
      <main>{children}</main>
    </div>
  );
};

// Main App Component
function App() {
  return (
    <div className="App">
      <BrowserRouter>
        <AuthProvider>
          <Routes>
            <Route path="/login" element={<LoginPage />} />
            <Route path="/dashboard" element={
              <ProtectedRoute>
                <Dashboard />
              </ProtectedRoute>
            } />
            <Route path="/schedule" element={
              <ProtectedRoute>
                <SchedulePage />
              </ProtectedRoute>
            } />
            <Route path="/time-tracking" element={
              <ProtectedRoute>
                <TimeTracking />
              </ProtectedRoute>
            } />
            <Route path="/leave" element={
              <ProtectedRoute>
                <LeavePage />
              </ProtectedRoute>
            } />
            <Route path="/chat" element={
              <ProtectedRoute>
                <ChatPage />
              </ProtectedRoute>
            } />
            <Route path="/employees" element={
              <ProtectedRoute>
                <EmployeesPage />
              </ProtectedRoute>
            } />
            <Route path="/" element={<Navigate to="/dashboard" replace />} />
          </Routes>
        </AuthProvider>
      </BrowserRouter>
    </div>
  );
}

export default App;