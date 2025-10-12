import React, { useState, useEffect } from 'react';
import ApiService from '../../services/api';

const EscalationManagement = ({ isOpen, onClose }) => {
  const [escalations, setEscalations] = useState([]);
  const [categories, setCategories] = useState([]);
  const [statuses, setStatuses] = useState([]);
  const [loading, setLoading] = useState(true);
  const [showCreateForm, setShowCreateForm] = useState(false);
  const [selectedEscalation, setSelectedEscalation] = useState(null);
  const [newEscalationData, setNewEscalationData] = useState({
    title: '',
    description: '',
    category_id: '',
    priority: 'medium',
    assigned_to: '',
    due_date: ''
  });
  const [filters, setFilters] = useState({
    category_id: '',
    status_id: '',
    priority: ''
  });

  useEffect(() => {
    if (isOpen) {
      fetchEscalations();
      fetchCategories();
      fetchStatuses();
    }
  }, [isOpen]);

  const fetchEscalations = async () => {
    setLoading(true);
    try {
      const response = await ApiService.getEscalations(filters);
      if (response.data && response.data.escalations) {
        setEscalations(response.data.escalations);
      }
    } catch (error) {
      console.error('Failed to fetch escalations:', error);
      alert('Failed to fetch escalations: ' + error.message);
    } finally {
      setLoading(false);
    }
  };

  const fetchCategories = async () => {
    try {
      const response = await ApiService.getEscalationCategories();
      if (response.data && response.data.categories) {
        setCategories(response.data.categories);
      }
    } catch (error) {
      console.error('Failed to fetch categories:', error);
    }
  };

  const fetchStatuses = async () => {
    try {
      const response = await ApiService.getEscalationStatuses();
      if (response.data && response.data.statuses) {
        setStatuses(response.data.statuses);
      }
    } catch (error) {
      console.error('Failed to fetch statuses:', error);
    }
  };

  const handleCreateEscalation = async (e) => {
    e.preventDefault();
    try {
      const response = await ApiService.createEscalation(newEscalationData);
      if (response.status === 200 || response.status === 201) {
        alert('Escalation created successfully!');
        setShowCreateForm(false);
        setNewEscalationData({
          title: '',
          description: '',
          category_id: '',
          priority: 'medium',
          assigned_to: '',
          due_date: ''
        });
        fetchEscalations();
      } else {
        alert('Failed to create escalation: ' + response.message);
      }
    } catch (error) {
      console.error('Error creating escalation:', error);
      alert('Error creating escalation: ' + error.message);
    }
  };

  const handleUpdateEscalation = async (escalationId, updateData) => {
    try {
      const response = await ApiService.updateEscalation(escalationId, updateData);
      if (response.status === 200) {
        alert('Escalation updated successfully!');
        fetchEscalations();
        setSelectedEscalation(null);
      } else {
        alert('Failed to update escalation: ' + response.message);
      }
    } catch (error) {
      console.error('Error updating escalation:', error);
      alert('Error updating escalation: ' + error.message);
    }
  };

  const handleDeleteEscalation = async (escalationId) => {
    if (window.confirm('Are you sure you want to delete this escalation?')) {
      try {
        const response = await ApiService.deleteEscalation(escalationId);
        if (response.status === 200) {
          alert('Escalation deleted successfully!');
          fetchEscalations();
        } else {
          alert('Failed to delete escalation: ' + response.message);
        }
      } catch (error) {
        console.error('Error deleting escalation:', error);
        alert('Error deleting escalation: ' + error.message);
      }
    }
  };

  const handleInputChange = (e) => {
    const { name, value } = e.target;
    setNewEscalationData(prev => ({
      ...prev,
      [name]: value
    }));
  };

  const handleFilterChange = (e) => {
    const { name, value } = e.target;
    setFilters(prev => ({
      ...prev,
      [name]: value
    }));
  };

  const applyFilters = () => {
    fetchEscalations();
  };

  const clearFilters = () => {
    setFilters({
      category_id: '',
      status_id: '',
      priority: ''
    });
  };

  const formatDate = (dateString) => {
    if (!dateString) return 'N/A';
    return new Date(dateString).toLocaleDateString();
  };

  const getPriorityClass = (priority) => {
    switch (priority) {
      case 'low': return 'priority-low';
      case 'medium': return 'priority-medium';
      case 'high': return 'priority-high';
      case 'critical': return 'priority-critical';
      default: return '';
    }
  };

  if (!isOpen) return null;

  return (
    <div className="modal-backdrop" onClick={onClose}>
      <div className="modal-content modal-large" onClick={(e) => e.stopPropagation()}>
        <div className="modal-header">
          <h2>🚨 Escalation Management</h2>
          <button className="modal-close" onClick={onClose}>&times;</button>
        </div>
        <div className="modal-body">
          <div className="notification-banner info">
            <p><strong>ℹ️ System Escalations:</strong> This is the central hub for managing high-priority system issues and escalations.</p>
            <p>Appointment and billing issues should be directed to their respective departments.</p>
          </div>
          
          <div className="escalation-management">
            {showCreateForm ? (
              <div className="create-escalation-form">
                <h3>Create New Escalation</h3>
                <form onSubmit={handleCreateEscalation} className="modal-form">
                  <div className="form-group">
                    <label>Title:</label>
                    <input
                      type="text"
                      name="title"
                      value={newEscalationData.title}
                      onChange={handleInputChange}
                      required
                    />
                  </div>
                  <div className="form-group">
                    <label>Description:</label>
                    <textarea
                      name="description"
                      value={newEscalationData.description}
                      onChange={handleInputChange}
                      required
                    />
                  </div>
                  <div className="form-group">
                    <label>Category:</label>
                    <select
                      name="category_id"
                      value={newEscalationData.category_id}
                      onChange={handleInputChange}
                      required
                    >
                      <option value="">Select Category</option>
                      {categories.map(category => (
                        <option key={category.id} value={category.id}>
                          {category.display_name}
                        </option>
                      ))}
                    </select>
                  </div>
                  <div className="form-group">
                    <label>Priority:</label>
                    <select
                      name="priority"
                      value={newEscalationData.priority}
                      onChange={handleInputChange}
                    >
                      <option value="low">Low</option>
                      <option value="medium">Medium</option>
                      <option value="high">High</option>
                      <option value="critical">Critical</option>
                    </select>
                  </div>
                  <div className="form-group">
                    <label>Due Date:</label>
                    <input
                      type="date"
                      name="due_date"
                      value={newEscalationData.due_date}
                      onChange={handleInputChange}
                    />
                  </div>
                  <div className="form-actions">
                    <button type="button" className="btn btn-secondary" onClick={() => setShowCreateForm(false)}>
                      Cancel
                    </button>
                    <button type="submit" className="btn btn-primary">
                      Create Escalation
                    </button>
                  </div>
                </form>
              </div>
            ) : selectedEscalation ? (
              <div className="escalation-details">
                <h3>Escalation Details</h3>
                <div className="escalation-info">
                  <div className="info-row">
                    <span className="label">Title:</span>
                    <span className="value">{selectedEscalation.title}</span>
                  </div>
                  <div className="info-row">
                    <span className="label">Category:</span>
                    <span className="value">{selectedEscalation.category_name}</span>
                  </div>
                  <div className="info-row">
                    <span className="label">Status:</span>
                    <span className="value">{selectedEscalation.status_name}</span>
                  </div>
                  <div className="info-row">
                    <span className="label">Priority:</span>
                    <span className={`value ${getPriorityClass(selectedEscalation.priority)}`}>
                      {selectedEscalation.priority}
                    </span>
                  </div>
                  <div className="info-row">
                    <span className="label">Reported By:</span>
                    <span className="value">{selectedEscalation.reporter_name} ({selectedEscalation.reporter_email})</span>
                  </div>
                  <div className="info-row">
                    <span className="label">Assigned To:</span>
                    <span className="value">
                      {selectedEscalation.assigned_to_name || 'Unassigned'}
                    </span>
                  </div>
                  <div className="info-row">
                    <span className="label">Created:</span>
                    <span className="value">{formatDate(selectedEscalation.created_at)}</span>
                  </div>
                  <div className="info-row">
                    <span className="label">Due Date:</span>
                    <span className="value">{formatDate(selectedEscalation.due_date)}</span>
                  </div>
                  <div className="info-row">
                    <span className="label">Description:</span>
                    <span className="value">{selectedEscalation.description}</span>
                  </div>
                  {selectedEscalation.resolution_notes && (
                    <div className="info-row">
                      <span className="label">Resolution Notes:</span>
                      <span className="value">{selectedEscalation.resolution_notes}</span>
                    </div>
                  )}
                </div>
                
                {selectedEscalation.comments && selectedEscalation.comments.length > 0 && (
                  <div className="comments-section">
                    <h4>Comments</h4>
                    <div className="comments-list">
                      {selectedEscalation.comments.map(comment => (
                        <div key={comment.id} className="comment-item">
                          <div className="comment-header">
                            <span className="comment-author">{comment.author_name}</span>
                            <span className="comment-date">{formatDate(comment.created_at)}</span>
                          </div>
                          <div className="comment-content">
                            {comment.comment}
                          </div>
                        </div>
                      ))}
                    </div>
                  </div>
                )}
                
                <div className="form-actions">
                  <button 
                    className="btn btn-secondary" 
                    onClick={() => setSelectedEscalation(null)}
                  >
                    Back to List
                  </button>
                </div>
              </div>
            ) : (
              <>
                <div className="section-header">
                  <h3>System Escalations</h3>
                  <button className="btn btn-primary" onClick={() => setShowCreateForm(true)}>
                    Create Escalation
                  </button>
                </div>
                
                <div className="filters-section">
                  <div className="filter-group">
                    <label>Category:</label>
                    <select
                      name="category_id"
                      value={filters.category_id}
                      onChange={handleFilterChange}
                    >
                      <option value="">All Categories</option>
                      {categories.map(category => (
                        <option key={category.id} value={category.id}>
                          {category.display_name}
                        </option>
                      ))}
                    </select>
                  </div>
                  
                  <div className="filter-group">
                    <label>Status:</label>
                    <select
                      name="status_id"
                      value={filters.status_id}
                      onChange={handleFilterChange}
                    >
                      <option value="">All Statuses</option>
                      {statuses.map(status => (
                        <option key={status.id} value={status.id}>
                          {status.display_name}
                        </option>
                      ))}
                    </select>
                  </div>
                  
                  <div className="filter-group">
                    <label>Priority:</label>
                    <select
                      name="priority"
                      value={filters.priority}
                      onChange={handleFilterChange}
                    >
                      <option value="">All Priorities</option>
                      <option value="low">Low</option>
                      <option value="medium">Medium</option>
                      <option value="high">High</option>
                      <option value="critical">Critical</option>
                    </select>
                  </div>
                  
                  <div className="filter-actions">
                    <button className="btn btn-secondary" onClick={clearFilters}>
                      Clear
                    </button>
                    <button className="btn btn-primary" onClick={applyFilters}>
                      Apply Filters
                    </button>
                  </div>
                </div>
                
                {loading ? (
                  <p>Loading escalations...</p>
                ) : (
                  <div className="escalations-table">
                    <table>
                      <thead>
                        <tr>
                          <th>Title</th>
                          <th>Category</th>
                          <th>Status</th>
                          <th>Priority</th>
                          <th>Reported</th>
                          <th>Due Date</th>
                          <th>Actions</th>
                        </tr>
                      </thead>
                      <tbody>
                        {escalations.map(escalation => (
                          <tr key={escalation.id}>
                            <td>{escalation.title}</td>
                            <td>{escalation.category_name}</td>
                            <td>{escalation.status_name}</td>
                            <td className={getPriorityClass(escalation.priority)}>
                              {escalation.priority}
                            </td>
                            <td>{formatDate(escalation.created_at)}</td>
                            <td>{formatDate(escalation.due_date)}</td>
                            <td>
                              <button 
                                className="btn btn-sm btn-secondary"
                                onClick={() => setSelectedEscalation(escalation)}
                              >
                                View
                              </button>
                              <button 
                                className="btn btn-sm btn-danger"
                                onClick={() => handleDeleteEscalation(escalation.id)}
                              >
                                Delete
                              </button>
                            </td>
                          </tr>
                        ))}
                      </tbody>
                    </table>
                    
                    {escalations.length === 0 && (
                      <div className="no-data">
                        <p>No escalations found.</p>
                      </div>
                    )}
                  </div>
                )}
              </>
            )}
          </div>
        </div>
      </div>
    </div>
  );
};

export default EscalationManagement;