import React, { useState, useEffect } from 'react';
import './Storage.css';

const DocumentList = () => {
  const [documents, setDocuments] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');

  useEffect(() => {
    fetchDocuments();
  }, []);

  const fetchDocuments = async () => {
    setLoading(true);
    setError('');
    
    try {
      // In a real app, this would be an API call
      // For now, we'll simulate with mock data
      const mockDocuments = [
        {
          id: 1,
          name: 'medical-report.pdf',
          type: 'application/pdf',
          size: 2048000,
          uploadedAt: '2023-08-15T10:30:00Z'
        },
        {
          id: 2,
          name: 'xray-image.jpg',
          type: 'image/jpeg',
          size: 1536000,
          uploadedAt: '2023-08-10T14:15:00Z'
        },
        {
          id: 3,
          name: 'lab-results.txt',
          type: 'text/plain',
          size: 102400,
          uploadedAt: '2023-08-05T09:45:00Z'
        }
      ];
      
      setDocuments(mockDocuments);
    } catch (err) {
      setError('Failed to fetch documents');
    } finally {
      setLoading(false);
    }
  };

  const handleDownload = async (documentId) => {
    // In a real app, this would be an API call to download the document
    // For now, we'll just show an alert
    alert(`Downloading document #${documentId}`);
  };

  const handleDelete = async (documentId) => {
    // In a real app, this would be an API call to delete the document
    // For now, we'll just update the local state
    setDocuments(prevDocuments => 
      prevDocuments.filter(doc => doc.id !== documentId)
    );
  };

  const getFileIconClass = (fileType) => {
    if (fileType.startsWith('image/')) {
      return 'file-icon image';
    } else if (fileType === 'application/pdf') {
      return 'file-icon pdf';
    } else {
      return 'file-icon text';
    }
  };

  const formatFileSize = (bytes) => {
    if (bytes < 1024) {
      return bytes + ' bytes';
    } else if (bytes < 1024 * 1024) {
      return (bytes / 1024).toFixed(1) + ' KB';
    } else {
      return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
    }
  };

  if (loading) {
    return <div className="documents-list">Loading documents...</div>;
  }

  if (error) {
    return <div className="documents-list error-message">{error}</div>;
  }

  return (
    <div className="documents-list">
      <h2>My Documents</h2>
      
      {documents.length === 0 ? (
        <p>No documents found.</p>
      ) : (
        <div className="documents-table">
          <div className="table-header">
            <div className="header-item">File</div>
            <div className="header-item">Type</div>
            <div className="header-item">Size</div>
            <div className="header-item">Uploaded</div>
            <div className="header-item">Actions</div>
          </div>
          
          {documents.map(document => (
            <div key={document.id} className="document-row">
              <div className="row-item">
                <span className={getFileIconClass(document.type)}></span>
                {document.name}
              </div>
              <div className="row-item">
                {document.type.split('/')[1].toUpperCase()}
              </div>
              <div className="row-item">
                {formatFileSize(document.size)}
              </div>
              <div className="row-item">
                {new Date(document.uploadedAt).toLocaleDateString()}
              </div>
              <div className="row-item document-actions">
                <button 
                  className="btn btn-primary"
                  onClick={() => handleDownload(document.id)}
                >
                  Download
                </button>
                <button 
                  className="btn btn-danger"
                  onClick={() => handleDelete(document.id)}
                >
                  Delete
                </button>
              </div>
            </div>
          ))}
        </div>
      )}
    </div>
  );
};

export default DocumentList;